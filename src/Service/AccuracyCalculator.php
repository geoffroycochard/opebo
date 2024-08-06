<?php
declare(strict_types=1);

namespace App\Service;

use App\Config\Objective;
use App\Entity\Domain;
use App\Entity\Request;
use App\Entity\Sponsorship;
use App\Repository\ProposalRepository;
use App\Repository\SponsorshipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Workflow\WorkflowInterface;

final class AccuracyCalculator
{
    public function __construct(
        private ProposalRepository $proposalRepository,
        private SponsorshipRepository $sponsorshipRepository,
        private EntityManagerInterface $entityManagerInterface,
        #[Target('sponsorship')]
        private WorkflowInterface $sponsorshipWorkflow,
        #[Target('lead')]
        private WorkflowInterface $leadWorkflow
    ) {
    }

    public function calculate(Request $request): array
    {
        $dataset = [];

        # Check if previous delete initialized sp set free proposals
        foreach ($request->getSponsorships() as $sponsorship) {
            $proposal = $sponsorship->getProposal();
            $this->leadWorkflow->apply($proposal, 'to_free');
            $this->entityManagerInterface->remove($sponsorship);
        }
        if (!$this->leadWorkflow->getMarking($request)->has('free')) {
            $this->leadWorkflow->apply($request, 'to_free');
        }
        $this->entityManagerInterface->flush();

        # TODO : check if available (status)
        $sponsors = [];
        foreach ($this->proposalRepository->findBy(['status' => 'free']) as $proposal) {
            // Check sponsor person has many proposals
            if (in_array($proposal->getPerson()->getId(), $sponsors)) {
                continue;
            }
            $sponsors[] = $proposal->getPerson()->getId();
            
            $d = [
                'gender' => array_map(
                    function ($gender) {
                        return $gender->value;
                    },
                    [$proposal->getPerson()->getGender()]
                ),
                'language' => $proposal->getLanguage(),
                'domain' => $proposal->getDomains()->map(function (Domain $domain) {
                    return $domain->getName();
                })->toArray(),
                'objective' => array_map(
                    function ($objective) {
                        return $objective->value;
                    },
                    $proposal->getObjective()
                ),
                'location' => [
                    'city' => $proposal->getPerson()->getCity()->getName(),
                    'lat' => $proposal->getPerson()->getCity()->getLat(),
                    'lng' => $proposal->getPerson()->getCity()->getLng()
                ]
            ];
            $dataset[$proposal->getId()] = $d;
        }

        $search = [
            'gender' => array_map(
                function ($gender) {
                    return $gender->value;
                },
                [$request->getPerson()->getGender()]
            ),
            'language' => $request->getLanguage(),
            'domain' => $request->getDomains()->map(function ($domain) {
                return $domain->getName();
            })->toArray(),
            'objective' => array_map(
                function ($objective) {
                    return $objective->value;
                },
                $request->getObjective()
            ),
            'location' => [
                'city' => $request->getPerson()->getCity()->getName(),
                'lat' => $request->getPerson()->getCity()->getLat(),
                'lng' => $request->getPerson()->getCity()->getLng()
            ]
        ];
        $this->leadWorkflow->apply($request, 'to_blocked');

        /** Depend Objective parameter */
        $kpis = [
            Objective::Exc->value => [
                'language' => 100,
                'gender' => 100,
                'objective' => 50,
                'domain' => 30,
                'location' => 100,
            ],
            Objective::Acc->value => [
                'language' => 100,
                'gender' => 100,
                'objective' => 100,
                'domain' => 10,
                'location' => 100,
            ],
            Objective::Adm->value => [
                'language' => 70,
                'gender' => 100,
                'objective' => 50,
                'domain' => 10,
                'location' => 50,
            ],
            Objective::Hfi->value => [
                'language' => 20,
                'gender' => 100,
                'objective' => 50,
                'domain' => 100,
                'location' => 10,
            ],
            Objective::Hfj->value => [
                'language' => 20,
                'gender' => 100,
                'objective' => 50,
                'domain' => 100,
                'location' => 10,
            ],
        ];

        $score = [];
        foreach ($dataset as $proposalId => $data) {
            $resume = [];
            $total = 0;
            $objectiveIntersect = array_intersect($search['objective'], $data['objective']);
            foreach ($objectiveIntersect as $objective) {
                $localKpis = $kpis[$objective];
                $totalObjective = 0;
                foreach ($localKpis as $kpi => $boost) {
                    $s = 0;
                    if ($kpi === 'location') {
                        $diameter = 60000; // 30km arround orléans
                        $distance = $this->getDistance(
                            $data[$kpi]['lat'],
                            $data[$kpi]['lng'],
                            $search[$kpi]['lat'],
                            $search[$kpi]['lng']
                        );
                        $s = round((($diameter - $distance) / 10000) * $boost);
                    } else {
                        $intersect = array_intersect($data[$kpi], $search[$kpi]);
                        $s = count($intersect) * $boost;
                    }
                    $resume[$objective]['kpis'][$kpi] = $s;
                    $totalObjective += $s;
                }
                $resume[$objective]['total'] = $totalObjective;
                $total += $totalObjective;
            }
            if ($total > 0) {
                $score[$proposalId] = [
                    'score' => $total,
                    'resume' => $resume
                ];
            }
        }
        uasort($score, function ($a, $b) {
            return $a['score'] <= $b['score'];
        });

        $score = array_slice($score, 0, 5, true);

        $initialPlace = $this->sponsorshipWorkflow->getDefinition()->getInitialPlaces();
        $initialPlace = array_pop($initialPlace);
        foreach ($score as $proposalId => $v) {
            $proposal = $this->proposalRepository->find($proposalId);
            $sponsorship = (new Sponsorship())
                ->setScore($v['score'])
                ->setResume($v['resume'])
                ->setProposal($proposal)
                ->setRequest($request)
                ->setStatus($initialPlace)
            ;
            $this->entityManagerInterface->persist($sponsorship);
            $this->leadWorkflow->apply($proposal, 'to_blocked');
        }

        $this->entityManagerInterface->flush();

        return $score;
    }

    /**
     * Calculates the distance between two points, given their 
     * latitude and longitude, and returns an array of values 
     * of the most common distance units
     *
     * @param  {coord} $lat1 Latitude of the first point
     * @param  {coord} $lon1 Longitude of the first point
     * @param  {coord} $lat2 Latitude of the second point
     * @param  {coord} $lon2 Longitude of the second point
     * @return {float}       Values in meter units
     */
    private function getDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $theta = $lon1 - $lon2;
        $miles = (sin(deg2rad($lat1)) * sin(deg2rad($lat2))) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)));
        $miles = acos($miles);
        $miles = rad2deg($miles);
        $miles = $miles * 60 * 1.1515;
        $kilometers = $miles * 1.609344;
        $meters = $kilometers * 1000;
        return $meters;
    }
}

<?php
declare(strict_types=1);

namespace App\Service;
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
    )
    {}


    public function calculate(Request $request): array
    {
        $dataset = [];
        # TODO : check if available (status)
        foreach ($this->proposalRepository->findBy([
            'status' => 'free'
        ]) as $proposal) {
            $d = [
                'gender' => array_map(function($gender) { 
                    return $gender->value; }, $proposal->getGender()
                ),
                'language' => array_map(function($language) { 
                    return $language->value; }, $proposal->getLanguage()
                ),
                'domain' => $proposal->getDomains()->map(function($domain){ 
                    return $domain->getName(); })->toArray(), 
                'objective' => array_map(function($objective) { 
                    return $objective->value; }, $proposal->getObjective()
                ),
            ];
            $dataset[$proposal->getId()] = $d;
            $this->leadWorkflow->apply($proposal, 'to_blocked');
        }
        
        $search = [
            'gender' => array_map(function($gender) { 
                return $gender->value; }, $request->getGender()
            ),
            'language' => array_map(function($language) { 
                return $language->value; }, $request->getLanguage()
            ),
            'domain' => $request->getDomains()->map(function($domain){ 
                return $domain->getName(); })->toArray(), 
            'objective' => array_map(function($objective) { 
                return $objective->value; }, $request->getObjective()
            ),
        ];
        $this->leadWorkflow->apply($request, 'to_blocked');

        /** Depend Objective parameter */
        $kpis = [
            'gender' => 100,
            'language' => 10, 
            'domain' => 30, 
            'objective' => 100
        ];

        $score = [];
        foreach ($dataset as $proposalId => $data) {
            $s = 0;
            $resume = [];
            foreach ($kpis as $kpi => $boost) {
                $intersect = array_intersect($data[$kpi], $search[$kpi]);
                $s += count($intersect) * $boost;
                $resume[$kpi] = $s;
            }
            $score[$proposalId] = $s / count($kpis);

            $initialPlace = $this->sponsorshipWorkflow->getDefinition()->getInitialPlaces();
            $sponsorship = (new Sponsorship())
                ->setScore($s)
                ->setResume($resume)
                ->setProposal($this->proposalRepository->find($proposalId))
                ->setRequest($request)
                ->setStatus(array_pop($initialPlace))
            ;
            $this->entityManagerInterface->persist($sponsorship);
        }
        $this->entityManagerInterface->flush();

        return $score;
    }
}

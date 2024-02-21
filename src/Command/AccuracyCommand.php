<?php

namespace App\Command;

use App\Entity\Sponsorship;
use Doctrine\ORM\EntityManager;
use App\Repository\RequestRepository;
use App\Repository\ProposalRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SponsorshipRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;


#[AsCommand(
    name: 'app:accuracy',
    description: 'Add a short description for your command',
)]
class AccuracyCommand extends Command
{

    public function __construct(
        private RequestRepository $requestRepository,
        private ProposalRepository $proposalRepository,
        private SponsorshipRepository $sponsorshipRepository,
        private EntityManagerInterface $entityManagerInterface,
        #[Target('sponsorship')]
        private WorkflowInterface $sponsorshipWorkflow,
        #[Target('lead')]
        private WorkflowInterface $leadWorkflow
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('request', InputArgument::REQUIRED, 'Request id')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $requestId = $input->getArgument('request');

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
        
        $request = $this->requestRepository->find($requestId);
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
                ->setStatus(array_flip($initialPlace))
            ;
            $this->entityManagerInterface->persist($sponsorship);
        }
        $this->entityManagerInterface->flush();

        dump($score);

        asort($score, SORT_NUMERIC);
        $score = array_reverse($score, true);
        
        $person = array_key_first($score);
        $io->note(sprintf('Best match is : %s', $person ));

        $io->success('Matching work.');

        return Command::SUCCESS;
    }
}

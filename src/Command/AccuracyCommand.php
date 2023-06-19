<?php

namespace App\Command;

use App\Entity\Sponsorship;
use App\Repository\ProposalRepository;
use App\Repository\RequestRepository;
use App\Repository\SponsorshipRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        private EntityManagerInterface $entityManagerInterface
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

        // $dataset = [
        //     'person 1' => [
        //         'gender' => ['male'],
        //         'language' => ['fr'], 
        //         'domain' => ['vente','marketing'],
        //         'objective' => ['hébergement']
        //     ],
        //     'person 2' => [
        //         'gender' => ['female'],
        //         'language' => ['fr','cn'], 
        //         'domain' => ['numérique','marketing'], 
        //         'objective' => ['hébergement']
        //     ],
        //     'person 3' => [
        //         'gender' => ['male'],
        //         'language' => ['fr'], 
        //         'domain' => ['vente','mécanique'], 
        //         'objective' => ['hébergement']
        //     ],
        //     'person 4' => [
        //         'gender' => ['male'],
        //         'language' => ['fr'], 
        //         'domain' => ['mécanique','marketing'], 
        //         'objective' => ['hébergement']
        //     ],
        //     'person 5' => [
        //         'gender' => ['female'],
        //         'language' => ['fr','cn'], 
        //         'domain' => ['mécanique', 'numérique', 'digital'], 
        //         'objective' => ['convivial']
        //     ],
        //     'person 6' => [
        //         'gender' => ['female'],
        //         'language' => ['fr','en'], 
        //         'domain' => ['mécanique','marketing'], 
        //         'objective' => ['hébergement']
        //     ],
        // ];

        // $search = [
        //     'gender' => ['female'],
        //     'language' => ['fr','cn'], 
        //     'domain' => ['mécanique','marketing'], 
        //     'objective' => ['convivial']
        // ];

        $dataset = [];
        # TODO : check if available (status)
        foreach ($this->proposalRepository->findAll() as $proposal) {
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

        /** Depend Objective parameter */
        $kpis = [
            'gender' => 100,
            'language' => 10, 
            'domain' => 30, 
            'objective' => 50
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
            $sponsoship = (new Sponsorship())
                ->setScore($s)
                ->setResume($resume)
                ->setProposal($this->proposalRepository->find($proposalId))
                ->setRequest($request)
            ;
            $this->entityManagerInterface->persist($sponsoship);
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

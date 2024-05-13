<?php

namespace App\Command;

use App\Entity\Sponsorship;
use App\Service\AccuracyCalculator;
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
        private AccuracyCalculator $accuracyCalculator
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

        $request = $this->requestRepository->find($requestId);

        if (!$request) {
            throw new \Exception(sprintf('Unable to find request %d id.', $requestId), 1);
        }

        $score = $this->accuracyCalculator->calculate($request);

        dump($score);

        asort($score, SORT_NUMERIC);
        $score = array_reverse($score, true);
        $person = array_key_first($score);
        $io->note(sprintf('Best match is : %s', $person ));
        $io->success('Matching work.');

        return Command::SUCCESS;
    }
}

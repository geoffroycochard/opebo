<?php

namespace App\Command;

use App\Entity\Sponsorship;
use App\Service\SponsorshipManager;
use App\Repository\SponsorshipRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:sponsorship',
    description: 'Add a short description for your command',
)]
class SponsorshipApprovedCommand extends Command
{
    public function __construct(
        private readonly SponsorshipRepository $sponsorshipRepository,
        private readonly SponsorshipManager $sponsorshipManager
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $validation = [
            'student_contacted',
            'sponsor_contacted'
        ];

        /** @var Sponsorship $sponsorship */
        foreach ($this->sponsorshipRepository->findAll() as $sponsorship) {
            if ($validation === array_keys($sponsorship->getStatus())) {
                $this->sponsorshipManager->validate($sponsorship, 'to_st_in_progress');
                $this->sponsorshipManager->validate($sponsorship, 'to_sp_in_progress');
            }
        }


        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}

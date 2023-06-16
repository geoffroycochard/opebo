<?php

namespace App\Command;

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
        $arg1 = $input->getArgument('arg1');

        $dataset = [
            'person 1' => [
                'gender' => ['male'],
                'language' => ['fr'], 
                'domain' => ['vente','marketing'],
                'objective' => ['hébergement']
            ],
            'person 2' => [
                'gender' => ['female'],
                'language' => ['fr','cn'], 
                'domain' => ['numérique','marketing'], 
                'objective' => ['hébergement']
            ],
            'person 3' => [
                'gender' => ['male'],
                'language' => ['fr'], 
                'domain' => ['vente','mécanique'], 
                'objective' => ['hébergement']
            ],
            'person 4' => [
                'gender' => ['male'],
                'language' => ['fr'], 
                'domain' => ['mécanique','marketing'], 
                'objective' => ['hébergement']
            ],
            'person 5' => [
                'gender' => ['female'],
                'language' => ['fr','cn'], 
                'domain' => ['mécanique', 'numérique', 'digital'], 
                'objective' => ['convivial']
            ],
            'person 6' => [
                'gender' => ['female'],
                'language' => ['fr','en'], 
                'domain' => ['mécanique','marketing'], 
                'objective' => ['hébergement']
            ],
        ];

        $search = [
            'gender' => ['female'],
            'language' => ['fr','cn'], 
            'domain' => ['mécanique','marketing'], 
            'objective' => ['convivial']
        ];

        /** Depend Objective parameter */
        $kpis = [
            'gender' => 100,
            'language' => 10, 
            'domain' => 30, 
            'objective' => 50
        ];

        $score = [];

        foreach ($dataset as $person => $data) {
            $s = 0;
            foreach ($kpis as $kpi => $boost) {
                $intersect = array_intersect($data[$kpi], $search[$kpi]);
                $s += count($intersect) * $boost;
            }
            $score[$person] = $s / count($kpis);
        }

        dump($score);

        asort($score, SORT_NUMERIC);
        $score = array_reverse($score, true);
        
        $person = array_key_first($score);
        $io->note(sprintf('Best match is : %s', $person ));

        $io->success('Matching work.');

        return Command::SUCCESS;
    }
}

<?php

namespace App\Command;

use App\Entity\Course;
use App\Entity\Domain;
use App\Entity\Establishment;
use App\Entity\Sector;
use App\Repository\CourseRepository;
use App\Repository\EstablishmentRepository;
use App\Repository\SectorRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:data:reference',
    description: 'Use to synchronise field / courses reference',
)]
class DataReferenceCommand extends Command
{
    private const CSV_FILE = 'data/referentiel.csv';

    private const EXCLUDE = [
        '', 'et', 'pour',
        'l\'', 'le', 'la', 'les', 'en',
        'un', 'une', 'des', 'd\'', 'de',
        'ce', 'cet', 'cette', 'ces',
        'mon', 'ton', 'son', 'ma', 'ta', 'sa', 'mes', 'tes', 'ses', 'notre', 'votre', 'leur', 'nos', 'vos', 'leurs',
        'quel', 'quelle', 'quels', 'quelles',
        'des', 'du', 'de la', 'de l\'', 'd\'',
        'of',
        ':', '/', '-'
    ];

    public function __construct(
        private EntityManagerInterface $entityManagerInterface,
        private EstablishmentRepository $establishmentRepository,
        private ValidatorInterface $validator
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        //load the CSV document from a file path
        $csv = Reader::createFromPath(self::CSV_FILE, 'r');
        $csv->setDelimiter(';');
        $csv->setHeaderOffset(0);

        $e = $s = $c = $k = [];

        foreach ($csv->getRecords() as $record) {
            if (empty($record['etablissement'])) {
                continue;
            }
            // dd($record);
            if (!array_key_exists($record['etablissement'], $e)) {
                $et = (new Establishment)
                    ->setName(trim($record['etablissement']))
                ;
                $errors = $this->validator->validate($et);
                if (count($errors) > 0) {
                    $et = $this->establishmentRepository->findOneBy([
                        'name' => trim($record['etablissement'])
                    ]);
                } else {
                    $this->entityManagerInterface->persist($et);
                }
                $e[$record['etablissement']] = $et;
            }
            
            $t = explode(';', $record['keywords']);
            foreach ($t as $i => $v) {
                $n = explode(',', $v);
                if (count($n) > 1) {
                    unset($t[$i]);
                    $t = array_merge($t, $n);
                }
            }

            $k = array_merge($k, array_map(fn($n) => strtolower(trim($n)), $t));
        }

        $k = array_filter($k);
        $k = array_unique($k);
        
        foreach ($k as $kw) {
            $kw = trim($kw);
            //$kw = preg_replace('/[^A-Za-z0-9\-]/', '', $kw); // Removes special chars.
            $kw = strtolower($kw);
            $kwe = (new Domain)->setName($kw);
            $errors = $this->validator->validate($kwe);
            if (count($errors) > 0) {
                $io->error(sprintf('ks %s already exist', $kw));
            } else {
                $this->entityManagerInterface->persist($kwe);
            }
        }

        $this->entityManagerInterface->flush(); 

        $io->success('All are synchronized.');

        return Command::SUCCESS;
    }
}

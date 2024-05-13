<?php

namespace App\Command;

use App\Entity\City;
use App\Repository\CityRepository;
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
    name: 'app:data:city',
    description: 'Use to synchronize city database from csv https://public.opendatasoft.com/explore/dataset/georef-france-commune (30kms)',
)]
class DataCityCommand extends Command
{
    // https://public.opendatasoft.com/explore/dataset/georef-france-commune/table/?flg=fr&disjunctive.reg_name&disjunctive.dep_name&disjunctive.arrdep_name&disjunctive.ze2020_name&disjunctive.bv2012_name&disjunctive.epci_name&disjunctive.ept_name&disjunctive.com_name&disjunctive.ze2010_name&disjunctive.com_is_mountain_area&q=&geofilter.distance=47.9029,1.9093,30000&disjunctive.bv2022_name&sort=year&refine.dep_name=Loiret&location=10,47.91818,2.12036&basemap=jawg.light
    private const CSV_FILE = 'data/georef-france-commune_ope.csv';


    public function __construct(
        private EntityManagerInterface $entityManagerInterface,
        private CityRepository $cityRepository,
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

        foreach ($csv->getRecords() as $record) {
            $a = explode(',', $record['Geo Point']);
            $city = (new City())
                ->setId($record['Code Officiel Commune'])
                ->setName($record['Nom Officiel Commune'])
                ->setShape($record['Geo Shape'])
                ->setLat($a[0])
                ->setLng($a[1])
            ;
            $errors = $this->validator->validate($city);
            if (count($errors)) {
                $io->error(sprintf('City %s already exist', $record['Nom Officiel Commune']));
            } else {
                $this->entityManagerInterface->persist($city);
            }
        }

        $this->entityManagerInterface->flush();

        $io->success('All new cities are synchronized with the csv reference.');

        return Command::SUCCESS;
    }
}

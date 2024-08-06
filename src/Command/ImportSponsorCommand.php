<?php

namespace App\Command;

use App\Config\Civility;
use App\Config\Gender;
use App\Config\Objective;
use App\Config\PersonStatus;
use App\Entity\Domain;
use App\Entity\Proposal;
use App\Entity\Request;
use App\Entity\Sponsor;
use App\Entity\Student;
use App\Repository\CityRepository;
use App\Repository\DomainRepository;
use App\Repository\EstablishmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Languages;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:import-sponsor',
    description: 'Use to import person (sponsor)',
)]
class ImportSponsorCommand extends Command
{

    static private $CSV_SPONSOR = 'data/db_sponsor.csv';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private EstablishmentRepository $establishmentRepository,
        private CityRepository $cityRepository,
        private DomainRepository $domainRepository,
        private ValidatorInterface $validator
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        //load the CSV document from a file path
        $csv = Reader::createFromPath(self::$CSV_SPONSOR, 'r');
        $csv->setDelimiter(';');
        $csv->setHeaderOffset(0);
        $io = new SymfonyStyle($input, $output);

        // $e = $s = $c = $k = [];
        // \Locale::setDefault('fr');
        // $c = Languages::getNames();
        // dd($c);

        foreach ($csv->getRecords() as $record) {
            // dd($record);
            $city = $this->cityRepository->find($record['city']);
            $birthday = \DateTime::createFromFormat('d/m/Y', $record['birthday']);

            $sponsor = (new Sponsor())
                ->setCivility(Civility::from($record['civility']))
                ->setFirstname($record['firstname'])
                ->setLastname($record['lastname'])
                ->setGender(Gender::from($record['gender']))
                ->setEmail($record['email'])
                ->setPhone('0'.$record['phone'])
                ->setBirthdate($birthday)
                ->setCity($city)
                ->setNationality('FR')
                ->setState(PersonStatus::Active)
            ;
            

            $errors = $this->validator->validate($sponsor);
            if (count($errors) > 0) {
                $io->error($errors);
                continue;
            } else {
                $this->entityManager->persist($sponsor);
            }

            $this->entityManager->persist($sponsor);

            $proposal = (new Proposal())
                ->setPerson($sponsor)
                ->setLanguage(array_merge(array_filter(explode(',', $record['languages'])), ['fr']))
                ->setStatus('free')
            ;

            $objs = [];
            foreach (Objective::cases() as $o) {
                if (
                    !empty($record[$o->value])
                ) {
                    $objs[] = $o;
                }
            }
            $proposal->setObjective($objs);

            // Manage domain
            if ($record['domain']) {
                foreach (explode(';', $record['domain']) as $domain) {
                    $domain = filter_var($domain, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW|FILTER_FLAG_STRIP_HIGH);
                    $domain = strtolower(trim($domain));
                    $ed = $this->domainRepository->findOneBy(['name' => $domain]);
                    if (!$ed) {
                        $ed = (new Domain())->setName($domain);
                        $this->entityManager->persist($ed);
                    }
                    $proposal->addDomain($ed);
                }
            }

            $this->entityManager->persist($proposal);

            // Many 
            if ($record['number'] === 'Oui') {
                $proposal = clone $proposal;
                $this->entityManager->persist($proposal);
            }
            
        }

        $this->entityManager->flush();
        $io->success('Sponsor import was done!');

        return Command::SUCCESS;
    }
}

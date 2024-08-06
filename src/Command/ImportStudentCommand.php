<?php

namespace App\Command;

use App\Config\Civility;
use App\Config\Gender;
use App\Config\Objective;
use App\Config\PersonStatus;
use App\Entity\Request;
use App\Entity\Student;
use App\Repository\CityRepository;
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
    name: 'app:import-student',
    description: 'Use to import person (student / sponsor)',
)]
class ImportStudentCommand extends Command
{

    static private $CSV_STUDENT = 'data/db_student.csv';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private EstablishmentRepository $establishmentRepository,
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
        $csv = Reader::createFromPath(self::$CSV_STUDENT, 'r');
        $csv->setDelimiter(';');
        $csv->setHeaderOffset(0);
        $io = new SymfonyStyle($input, $output);

        foreach ($csv->getRecords() as $record) {

            $city = $this->cityRepository->find($record['city']);
            $establishment = $this->establishmentRepository->find($record['establishment']);
            $birthday = \DateTime::createFromFormat('d/m/Y', $record['birthday']);

            $student = (new Student())
                ->setCivility(Civility::from($record['civility']))
                ->setFirstname($record['firstname'])
                ->setLastname($record['lastname'])
                ->setGender(Gender::from($record['gender']))
                ->setEmail($record['email'])
                ->setPhone($record['phone'])
                ->setBirthdate($birthday)
                ->setCity($city)
                ->setEstablishment($establishment)
                ->setNationality($record['nationality'])
                ->setState(PersonStatus::Active)
            ;

            $errors = $this->validator->validate($student);
            if (count($errors) > 0) {
                $io->error($errors);
                continue;
            } else {
                $this->entityManager->persist($student);
            }

            $request = (new Request())
                ->setPerson($student)
                ->setLanguage(explode(',', $record['languages']))
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
            $request->setObjective($objs);
            $this->entityManager->persist($request);
            
        }

        $this->entityManager->flush();
        $io->success('Student imported.');

        return Command::SUCCESS;
    }
}

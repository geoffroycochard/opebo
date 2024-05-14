<?php

namespace App\DataFixtures;

use App\Config\Civility;
use App\Config\Gender;
use App\Config\PersonStatus;
use App\Entity\City;
use App\Entity\Course;
use App\Entity\Sponsor;
use App\Entity\Student;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;

class PersonFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        $cities = ($manager->getRepository(City::class))->findAll();
        $courses = ($manager->getRepository(Course::class))->findAll();

        $faker = Faker\Factory::create('fr_FR');

        for ($i=0; $i < 15; $i++) { 
            $student = new Student();
            $student
                ->setGender(Gender::cases()[array_rand(Gender::cases())])
                ->setCivility(Civility::cases()[array_rand(Civility::cases())])
                ->setFirstname($faker->firstName())
                ->setLastname($faker->lastName())
                ->setPhone($faker->phoneNumber())
                ->setEmail($faker->email())
                ->setCity($cities[array_rand($cities, 1)])
                ->setCourse($courses[array_rand($courses, 1)])
                ->setState(PersonStatus::Active)
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable())
            ;
            $manager->persist($student);
        }
        

        for ($i=0; $i < 35; $i++) { 
            $sponsor = new Sponsor();
            $sponsor
                ->setGender(Gender::cases()[array_rand(Gender::cases())])
                ->setCivility(Civility::cases()[array_rand(Civility::cases())])
                ->setFirstname($faker->firstName())
                ->setLastname($faker->lastName())
                ->setPhone($faker->phoneNumber())
                ->setEmail($faker->email())
                ->setCity($cities[array_rand($cities, 1)])
                ->setState(PersonStatus::Active)
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable())
            ;
            $manager->persist($sponsor);
        }

        $manager->flush();
    }
}

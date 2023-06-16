<?php

namespace App\DataFixtures;

use App\Entity\Sponsor;
use App\Entity\Student;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;

class PersonFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        $faker = Faker\Factory::create('fr_FR');

        for ($i=0; $i < 5; $i++) { 
            $student = new Student();
            $student
                ->setCivility($faker->title())
                ->setFirstname($faker->firstName())
                ->setLastname($faker->lastName())
                ->setPhone($faker->phoneNumber())
                ->setState('valid')
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable())
            ;
            $manager->persist($student);
        }
        

        for ($i=0; $i < 5; $i++) { 
            $sponsor = new Sponsor();
            $sponsor
                ->setCivility($faker->title())
                ->setFirstname($faker->firstName())
                ->setLastname($faker->lastName())
                ->setPhone($faker->phoneNumber())
                ->setState('valid')
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable())
            ;
            $manager->persist($sponsor);
        }

        $manager->flush();
    }
}

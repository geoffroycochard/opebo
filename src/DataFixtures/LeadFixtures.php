<?php

namespace App\DataFixtures;

use App\Config\Gender;
use App\Config\Language;
use App\Config\Objective;
use App\Entity\Domain;
use App\Entity\Proposal;
use App\Entity\Request;
use App\Entity\Sponsor;
use App\Entity\Student;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;

/**
 * Summary of LeadFixtures
 */
class LeadFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * Summary of load
     * @param \Doctrine\Persistence\ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $domainListing = ($manager->getRepository(Domain::class))->findAll();


        foreach ([
                Student::class => Request::class, 
                Sponsor::class => Proposal::class
            ] as $personClass => $leadClass) {
            $persons = ($manager->getRepository($personClass))
            ->findAll();

            foreach ($persons as $person) {
                $domains = new ArrayCollection();
                foreach (array_rand($domainListing,rand(2,7)) as $key) {
                    $domains->add($domainListing[$key]);
                }
                $request = (new $leadClass)
                    ->setLanguage($this->rand(Language::cases(), rand(1,2)))
                    ->setObjective($this->rand(Objective::cases(), rand(1,3)))
                    ->setGender($this->rand(Gender::cases(), 1))
                    ->setPerson($person)
                    ->setDomains($domains)
                    ->setStatus('free')
                ;
                $manager->persist($request);
            }   
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            DomainFixtures::class,
            PersonFixtures::class
        ];
    }

    private function rand(array $array, int $number = 1): array 
    {
        $data = [];
        $final = array_rand($array,$number);
        if (is_int($final)) {
            $list[] = $final;
        } else {
            $list = $final;
        }
        foreach ($list as $v) {
            $data[] = $array[$v];
        }
        return $data;
    }
}

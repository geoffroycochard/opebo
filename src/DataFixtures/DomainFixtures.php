<?php

namespace App\DataFixtures;

use App\Entity\Domain;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class DomainFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $domains = [
            'Licence', 'Sciences', 'Techniques', 'Physique', 'Parcours', 'Motricité', 'Aménagement', 'Management',
            'Ingénierie', 'Thérapeutique', 'Cosmétique'
        ];

        foreach ($domains as $domain) {
            $manager->persist((new Domain)->setName($domain));
        }
        $manager->flush();

    }
}

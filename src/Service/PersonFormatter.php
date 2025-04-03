<?php

namespace App\Service;

use App\Entity\Person;
use Symfony\Contracts\Translation\TranslatorInterface;

class PersonFormatter
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    public function getFullName(Person $person, string $locale = 'fr'): string
    {
        $civility = $this->translator->trans(
            'app.civility.' . strtolower($person->getCivility()->name),
            locale: $locale
        );

        return sprintf(
            '%s %s %s',
            $civility,
            $person->getFirstName(),
            $person->getLastName()
        );
    }
} 
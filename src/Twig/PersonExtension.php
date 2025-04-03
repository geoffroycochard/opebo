<?php

namespace App\Twig;

use App\Entity\Person;
use App\Service\PersonFormatter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PersonExtension extends AbstractExtension
{
    public function __construct(
        private readonly PersonFormatter $personFormatter
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('person_full_name', [$this, 'getFullName']),
        ];
    }

    public function getFullName(Person $person, string $locale = 'fr'): string
    {
        return $this->personFormatter->getFullName($person, $locale);
    }
} 
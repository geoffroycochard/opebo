<?php
declare(strict_types=1);

namespace App\Dto;

use App\Entity\Course;
use Symfony\Component\Validator\Constraints as Assert;

final class RegisterDto 
{
    public function __construct(
        #[Assert\Choice(['student', 'sponsor'])]
        public readonly string $type,
        #[Assert\NotBlank]
        #[Assert\Email]
        public readonly string $email,
        #[Assert\Choice(['mr', 'mrs'])]
        public readonly string $civility,
        #[Assert\NotBlank]
        public readonly string $firstname,
        #[Assert\NotBlank]
        public readonly string $lastname,
        #[Assert\NotBlank]
        public readonly string $nationality,
        #[Assert\NotBlank]
        #[Assert\Date]
        public readonly string $birthdate,
        #[Assert\NotBlank]
        #[Assert\Type('integer')]
        public readonly int $city,
        #[Assert\NotBlank]
        public readonly string $phone,
        #[Assert\NotBlank]
        public readonly string $domains,
        #[Assert\NotBlank]
        public readonly string $objectives,
        #[Assert\NotBlank]
        public readonly string $languages,
        public readonly int $establishment,
        public readonly int $studyLevel,
        #[Assert\NotBlank]
        #[Assert\Choice([1,2])]
        public readonly int $proposalNumber,
    )
    {}
}


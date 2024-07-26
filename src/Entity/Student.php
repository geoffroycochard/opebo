<?php

namespace App\Entity;

use App\Repository\StudentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StudentRepository::class)]
class Student extends Person
{
    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $studyLevel = null;

    #[ORM\ManyToOne(inversedBy: 'students')]
    private ?Establishment $establishment = null;

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     * @return void
     */
    public function eraseCredentials() {
    }
    
    /**
     * Returns the roles granted to the user.
     *
     * public function getRoles()
     * {
     * return ['ROLE_USER'];
     * }
     *
     * Alternatively, the roles might be stored in a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     * @return string[]
     */
    public function getRoles(): array {
        return ['ROLE_PERSON'];
    }
    
    /**
     * Returns the identifier for this user (e.g. username or email address).
     * @return string
     */
    public function getUserIdentifier(): string {
        return $this->getEmail();
    }

    public function getStudyLevel(): ?int
    {
        return $this->studyLevel;
    }

    public function setStudyLevel(?int $studyLevel): static
    {
        $this->studyLevel = $studyLevel;

        return $this;
    }

    public function getEstablishment(): ?Establishment
    {
        return $this->establishment;
    }

    public function setEstablishment(?Establishment $establishment): static
    {
        $this->establishment = $establishment;

        return $this;
    }
}

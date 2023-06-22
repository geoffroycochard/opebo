<?php

namespace App\Entity;

use App\Config\Gender;
use App\Config\Language;
use App\Config\Objective;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\LeadRepository;

#[ORM\Entity(repositoryClass: LeadRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap(['proposal' => Proposal::class, 'request' => Request::class])]
abstract class Lead
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'leads')]
    private ?Person $person = null;

    #[ORM\Column(length: 255, enumType: Gender::class)]
    private array $gender = [];

    #[ORM\Column]
    private array $domain = [];

    #[ORM\Column(length: 255, enumType: Language::class)]
    private array $language = [];

    #[ORM\Column(length: 255, enumType: Objective::class)]
    private array $objective = [];

    #[ORM\ManyToMany(targetEntity: Domain::class, inversedBy: 'leads')]
    private Collection $domains;

    public function __construct()
    {
        $this->domains = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): static
    {
        $this->person = $person;

        return $this;
    }

    public function getGender(): array
    {
        return $this->gender;
    }

    public function setGender(array $gender): static
    {
        $this->gender = $gender;

        return $this;
    }

    public function getLanguage(): array
    {
        return $this->language;
    }

    public function setLanguage(array $language): static
    {
        $this->language = $language;

        return $this;
    }

    public function getObjective(): array
    {
        return $this->objective;
    }

    public function setObjective(array $objective): static
    {
        $this->objective = $objective;

        return $this;
    }

    /**
     * @return Collection<int, Domain>
     */
    public function getDomains(): Collection
    {
        return $this->domains;
    }

    public function addDomain(Domain $domain): static
    {
        if (!$this->domains->contains($domain)) {
            $this->domains->add($domain);
        }

        return $this;
    }

    public function setDomains(Collection $domains): static
    {
        $this->domains = $domains;
        return $this;
    }

    public function removeDomain(Domain $domain): static
    {
        $this->domains->removeElement($domain);

        return $this;
    }

}

<?php

namespace App\Entity;

use App\Repository\EstablishmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: EstablishmentRepository::class)]
#[UniqueEntity('name')]
class Establishment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'establishment', targetEntity: Sector::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $sectors;

    public function __construct()
    {
        $this->sectors = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Sector>
     */
    public function getSectors(): Collection
    {
        return $this->sectors;
    }

    public function addSector(Sector $sector): static
    {
        if (!$this->sectors->contains($sector)) {
            $this->sectors->add($sector);
            $sector->setEstablishment($this);
        }

        return $this;
    }

    public function removeSector(Sector $sector): static
    {
        if ($this->sectors->removeElement($sector)) {
            // set the owning side to null (unless already changed)
            if ($sector->getEstablishment() === $this) {
                $sector->setEstablishment(null);
            }
        }

        return $this;
    }
}

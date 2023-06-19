<?php

namespace App\Entity;

use App\Repository\RequestRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RequestRepository::class)]
class Request extends Lead
{
    #[ORM\OneToMany(mappedBy: 'request', targetEntity: Sponsorship::class)]
    private Collection $sponsorships;

    public function __construct()
    {
        parent::__construct();
        $this->sponsorships = new ArrayCollection();
    }

    /**
     * @return Collection<int, Sponsorship>
     */
    public function getSponsorships(): Collection
    {
        return $this->sponsorships;
    }

    public function addSponsorship(Sponsorship $sponsorship): static
    {
        if (!$this->sponsorships->contains($sponsorship)) {
            $this->sponsorships->add($sponsorship);
            $sponsorship->setRequest($this);
        }

        return $this;
    }

    public function removeSponsorship(Sponsorship $sponsorship): static
    {
        if ($this->sponsorships->removeElement($sponsorship)) {
            // set the owning side to null (unless already changed)
            if ($sponsorship->getRequest() === $this) {
                $sponsorship->setRequest(null);
            }
        }

        return $this;
    }
}

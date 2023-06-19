<?php

namespace App\Entity;

use App\Repository\ProposalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProposalRepository::class)]
class Proposal extends Lead
{
    #[ORM\OneToMany(mappedBy: 'proposal', targetEntity: Sponsorship::class)]
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
            $sponsorship->setProposal($this);
        }

        return $this;
    }

    public function removeSponsorship(Sponsorship $sponsorship): static
    {
        if ($this->sponsorships->removeElement($sponsorship)) {
            // set the owning side to null (unless already changed)
            if ($sponsorship->getProposal() === $this) {
                $sponsorship->setProposal(null);
            }
        }

        return $this;
    }
}

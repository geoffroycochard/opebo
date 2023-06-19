<?php

namespace App\Entity;

use App\Repository\SponsorshipRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SponsorshipRepository::class)]
class Sponsorship
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $score = null;

    #[ORM\Column]
    private array $resume = [];

    #[ORM\ManyToOne(inversedBy: 'sponsorships')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Request $request = null;

    #[ORM\ManyToOne(inversedBy: 'sponsorships')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Proposal $proposal = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScore(): ?float
    {
        return $this->score;
    }

    public function setScore(float $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getResume(): array
    {
        return $this->resume;
    }

    public function setResume(array $resume): static
    {
        $this->resume = $resume;

        return $this;
    }

    public function getRequest(): ?Request
    {
        return $this->request;
    }

    public function setRequest(?Request $request): static
    {
        $this->request = $request;

        return $this;
    }

    public function getProposal(): ?Proposal
    {
        return $this->proposal;
    }

    public function setProposal(?Proposal $proposal): static
    {
        $this->proposal = $proposal;

        return $this;
    }
}

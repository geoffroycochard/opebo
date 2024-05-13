<?php

namespace App\Entity;

use App\Repository\PersonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PersonRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap(['sponsor' => Sponsor::class, 'student' => Student::class])]
#[HasLifecycleCallbacks]
/**
 * Summary of Person
 */
abstract class Person implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /**
     * Summary of id
     * @var 
     */
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    /**
     * Summary of civility
     * @var 
     */
    private ?string $civility = null;

    #[ORM\Column(length: 255)]
    /**
     * Summary of firstname
     * @var 
     */
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    /**
     * Summary of lastname
     * @var 
     */
    private ?string $lastname = null;

    #[ORM\Column(length: 255)]
    /**
     * Summary of phone
     * @var 
     */
    private ?string $phone = null;

    #[ORM\Column(length: 255)]
    /**
     * Summary of state
     * @var 
     */
    private ?string $state = null;

    #[ORM\Column]
    /**
     * Summary of createdAt
     * @var 
     */
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    /**
     * Summary of updatedAt
     * @var 
     */
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: Lead::class)]
    /**
     * Summary of leads
     * @var Collection
     */
    private Collection $leads;

    #[ORM\Column(length: 255)]
    #[Assert\Email]
    #[Assert\Unique]
    /**
     * Summary of email
     * @var 
     */
    private ?string $email = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    /**
     * Summary of birthdate
     * @var 
     */
    private ?\DateTimeInterface $birthdate = null;

    /**
     * Summary of __construct
     */
    public function __construct()
    {
        $this->leads = new ArrayCollection();
    }

    /**
     * Summary of getId
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Summary of getCivility
     * @return string|null
     */
    public function getCivility(): ?string
    {
        return $this->civility;
    }

    /**
     * Summary of setCivility
     * @param string $civility
     * @return Person
     */
    public function setCivility(string $civility): static
    {
        $this->civility = $civility;

        return $this;
    }

    /**
     * Summary of getFirstname
     * @return string|null
     */
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    /**
     * Summary of setFirstname
     * @param string $firstname
     * @return Person
     */
    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Summary of getLastname
     * @return string|null
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    /**
     * Summary of setLastname
     * @param string $lastname
     * @return Person
     */
    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Summary of getPhone
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * Summary of setPhone
     * @param string $phone
     * @return Person
     */
    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Summary of getState
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * Summary of setState
     * @param string $state
     * @return Person
     */
    public function setState(string $state): static
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Summary of getCreatedAt
     * @return \DateTimeImmutable|null
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Summary of setCreatedAt
     * @param \DateTimeImmutable $createdAt
     * @return Person
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Summary of getUpdatedAt
     * @return \DateTimeImmutable|null
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Summary of setUpdatedAt
     * @param \DateTimeImmutable $updatedAt
     * @return Person
     */
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, Lead>
     */
    public function getLeads(): Collection
    {
        return $this->leads;
    }

    /**
     * Summary of addLead
     * @param \App\Entity\Lead $lead
     * @return Person
     */
    public function addLead(Lead $lead): static
    {
        if (!$this->leads->contains($lead)) {
            $this->leads->add($lead);
            $lead->setPerson($this);
        }

        return $this;
    }

    /**
     * Summary of removeLead
     * @param \App\Entity\Lead $lead
     * @return Person
     */
    public function removeLead(Lead $lead): static
    {
        if ($this->leads->removeElement($lead)) {
            // set the owning side to null (unless already changed)
            if ($lead->getPerson() === $this) {
                $lead->setPerson(null);
            }
        }

        return $this;
    }

    /**
     * Summary of getEmail
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Summary of setEmail
     * @param string $email
     * @return Person
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Summary of getBirthdate
     * @return \DateTimeInterface|null
     */
    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    /**
     * Summary of setBirthdate
     * @param mixed $birthdate
     * @return Person
     */
    public function setBirthdate(?\DateTimeInterface $birthdate): static
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    #[ORM\PrePersist]
    /**
     * Summary of setCreatedAtValue
     * @return void
     */
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->setUpdatedAtValue();
    }

    #[ORM\PreUpdate]
    /**
     * Summary of setUpdatedAtValue
     * @return void
     */
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
    
}

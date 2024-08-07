<?php

namespace App\Entity;

use App\Config\Civility;
use App\Config\Gender;
use App\Config\PersonStatus;
use App\Repository\PersonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PersonRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap(['sponsor' => Sponsor::class, 'student' => Student::class])]
#[HasLifecycleCallbacks]
#[UniqueEntity('email')]
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
    protected ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255, enumType: Gender::class)]
    protected ?Gender $gender = null;

    #[ORM\Column(type: Types::STRING, length: 255, enumType: Civility::class)]
    /**
     * Summary of civility
     * @var 
     */
    protected ?Civility $civility = null;

    #[ORM\Column(length: 255)]
    /**
     * Summary of firstname
     * @var 
     */
    protected ?string $firstname = null;

    #[ORM\Column(length: 255)]
    /**
     * Summary of lastname
     * @var 
     */
    protected ?string $lastname = null;

    #[ORM\Column(length: 255)]
    /**
     * Summary of phone
     * @var 
     */
    protected ?string $phone = null;

    #[ORM\Column(type: Types::STRING, length: 255, enumType: PersonStatus::class)]
    /**
     * Summary of state
     * @var 
     */
    protected ?PersonStatus $state = null;

    #[ORM\Column]
    /**
     * Summary of createdAt
     * @var 
     */
    protected ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    /**
     * Summary of updatedAt
     * @var 
     */
    protected ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: Lead::class)]
    /**
     * Summary of leads
     * @var Collection
     */
    protected Collection $leads;

    #[ORM\Column(length: 255)]
    #[Assert\Email]
    /**
     * Summary of email
     * @var 
     */
    protected ?string $email = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    /**
     * Summary of birthdate
     * @var 
     */
    protected ?\DateTimeInterface $birthdate = null;

    #[ORM\ManyToOne(inversedBy: 'persons')]
    #[ORM\JoinColumn(nullable: false)]
    protected ?City $city = null;

    #[ORM\Column(length: 8)]
    protected ?string $nationality = null;

    /**
     * Summary of __construct
     */
    public function __construct()
    {
        $this->leads = new ArrayCollection();
    }

    public function getFullname(): string
    {
        return implode(' ', [
            $this->getCivility()->name,
            $this->getFirstname(),
            $this->getLastname(),
            $this->getGender()->name,
        ]);
    }

    /**
     * Summary of getId
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGender(): ?Gender
    {
        return $this->gender;
    }

    public function setGender(Gender $gender): static
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Summary of getCivility
     * @return Civility|null
     */
    public function getCivility(): ?Civility
    {
        return $this->civility;
    }

    /**
     * Summary of setCivility
     * @param Civility $civility
     * @return Person
     */
    public function setCivility(Civility $civility): static
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
    public function getState(): ?PersonStatus
    {
        return $this->state;
    }

    /**
     * Summary of setState
     * @param PersonStatus $state
     * @return Person
     */
    public function setState(PersonStatus $state): static
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

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    public function setNationality(string $nationality): static
    {
        $this->nationality = $nationality;

        return $this;
    }
    
}

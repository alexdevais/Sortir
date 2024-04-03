<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique:true)]
    #[Assert\NotBlank]
    #[Assert\Length(min:3, minMessage: 'The name should be longer')]
    #[Assert\Length(max:255, maxMessage: 'The name should be shorter')]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank]
    #[Assert\GreaterThan('today')]
    private ?\DateTimeInterface $firstAirDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank]
    #[Assert\GreaterThan(propertyPath:'firstAirDate')]
    private ?\DateTimeInterface $duration = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank]
    #[Assert\GreaterThan(propertyPath:'firstAirDate')]
    private ?\DateTimeInterface $dateLimitationInscription = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    private ?int $nbInscriptionMax = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(min:3, minMessage: 'The name should be longer')]
    #[Assert\Length(max:500, maxMessage: 'The name should be shorter')]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private ?string $state = null;

    #[ORM\ManyToOne(cascade: ['persist','remove'], inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Location $location = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'events')]
    private Collection $user;

    #[ORM\ManyToOne(inversedBy: 'organizerEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $organizer = null;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="organizedEvents")
     * @ORM\JoinColumn(nullable=true)
     */



    public function __construct()
    {
        $this->user = new ArrayCollection();
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

    public function getFirstAirDate(): ?\DateTimeInterface
    {
        return $this->firstAirDate;
    }

    public function setFirstAirDate(\DateTimeInterface $firstAirDate): static
    {
        $this->firstAirDate = $firstAirDate;

        return $this;
    }

    public function getDuration(): ?\DateTimeInterface
    {
        return $this->duration;
    }

    public function setDuration(\DateTimeInterface $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDateLimitationInscription(): ?\DateTimeInterface
    {
        return $this->dateLimitationInscription;
    }

    public function setDateLimitationInscription(\DateTimeInterface $dateLimitationInscription): static
    {
        $this->dateLimitationInscription = $dateLimitationInscription;

        return $this;
    }

    public function getNbInscriptionMax(): ?int
    {
        return $this->nbInscriptionMax;
    }

    public function setNbInscriptionMax(int $nbInscriptionMax): static
    {
        $this->nbInscriptionMax = $nbInscriptionMax;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): static
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUser(): Collection
    {
        return $this->user;
    }

    public function addUser(User $user): static
    {
        if (!$this->user->contains($user)) {
            $this->user->add($user);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        $this->user->removeElement($user);

        return $this;
    }

 

    public function addParticipant(User $participant): self
    {
        if (!$this->user->contains($participant)) {
            $this->user->add($participant);
            $participant->addEvent($this);
        }

        return $this;
    }

    public function removeParticipant(User $participant): self
    {
        if ($this->user->contains($participant)) {
            $this->user->removeElement($participant);
            $participant->removeEvent($this);
        }

        return $this;
    }

    public function getParticipants(): Collection
    {
        return $this->user;
    }

    public function getOrganizer(): ?User
    {
        return $this->organizer;
    }

    public function setOrganizer(?User $organizer): static
    {
        $this->organizer = $organizer;

        return $this;
    }
}

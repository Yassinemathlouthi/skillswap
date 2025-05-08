<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $username = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatar = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $registeredAt = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserSkillOffered::class, cascade: ['persist', 'remove'])]
    private Collection $skillsOffered;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserSkillWanted::class, cascade: ['persist', 'remove'])]
    private Collection $skillsWanted;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserAvailability::class, cascade: ['persist', 'remove'])]
    private Collection $availabilities;

    #[ORM\OneToMany(mappedBy: 'fromUser', targetEntity: Session::class)]
    private Collection $sessionsRequested;

    #[ORM\OneToMany(mappedBy: 'toUser', targetEntity: Session::class)]
    private Collection $sessionsReceived;

    #[ORM\OneToMany(mappedBy: 'sender', targetEntity: Message::class)]
    private Collection $messagesSent;

    #[ORM\OneToMany(mappedBy: 'receiver', targetEntity: Message::class)]
    private Collection $messagesReceived;

    #[ORM\OneToMany(mappedBy: 'reviewer', targetEntity: Review::class)]
    private Collection $reviewsGiven;

    #[ORM\OneToMany(mappedBy: 'reviewedUser', targetEntity: Review::class)]
    private Collection $reviewsReceived;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->skillsOffered = new ArrayCollection();
        $this->skillsWanted = new ArrayCollection();
        $this->availabilities = new ArrayCollection();
        $this->sessionsRequested = new ArrayCollection();
        $this->sessionsReceived = new ArrayCollection();
        $this->messagesSent = new ArrayCollection();
        $this->messagesReceived = new ArrayCollection();
        $this->reviewsGiven = new ArrayCollection();
        $this->reviewsReceived = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): static
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $bio;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getRegisteredAt(): ?\DateTimeInterface
    {
        return $this->registeredAt;
    }

    public function setRegisteredAt(?\DateTimeInterface $registeredAt): static
    {
        $this->registeredAt = $registeredAt;

        return $this;
    }

    /**
     * @return Collection<int, UserSkillOffered>
     */
    public function getSkillsOffered(): Collection
    {
        return $this->skillsOffered;
    }

    public function addSkillOffered(UserSkillOffered $skillOffered): static
    {
        if (!$this->skillsOffered->contains($skillOffered)) {
            $this->skillsOffered->add($skillOffered);
            $skillOffered->setUser($this);
        }

        return $this;
    }

    public function removeSkillOffered(UserSkillOffered $skillOffered): static
    {
        if ($this->skillsOffered->removeElement($skillOffered)) {
            // set the owning side to null (unless already changed)
            if ($skillOffered->getUser() === $this) {
                $skillOffered->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserSkillWanted>
     */
    public function getSkillsWanted(): Collection
    {
        return $this->skillsWanted;
    }

    public function addSkillWanted(UserSkillWanted $skillWanted): static
    {
        if (!$this->skillsWanted->contains($skillWanted)) {
            $this->skillsWanted->add($skillWanted);
            $skillWanted->setUser($this);
        }

        return $this;
    }

    public function removeSkillWanted(UserSkillWanted $skillWanted): static
    {
        if ($this->skillsWanted->removeElement($skillWanted)) {
            // set the owning side to null (unless already changed)
            if ($skillWanted->getUser() === $this) {
                $skillWanted->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserAvailability>
     */
    public function getAvailabilities(): Collection
    {
        return $this->availabilities;
    }

    public function addAvailability(UserAvailability $availability): static
    {
        if (!$this->availabilities->contains($availability)) {
            $this->availabilities->add($availability);
            $availability->setUser($this);
        }

        return $this;
    }

    public function removeAvailability(UserAvailability $availability): static
    {
        if ($this->availabilities->removeElement($availability)) {
            // set the owning side to null (unless already changed)
            if ($availability->getUser() === $this) {
                $availability->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Session>
     */
    public function getSessionsRequested(): Collection
    {
        return $this->sessionsRequested;
    }

    public function addSessionRequested(Session $session): static
    {
        if (!$this->sessionsRequested->contains($session)) {
            $this->sessionsRequested->add($session);
            $session->setFromUser($this);
        }

        return $this;
    }

    public function removeSessionRequested(Session $session): static
    {
        if ($this->sessionsRequested->removeElement($session)) {
            // set the owning side to null (unless already changed)
            if ($session->getFromUser() === $this) {
                $session->setFromUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Session>
     */
    public function getSessionsReceived(): Collection
    {
        return $this->sessionsReceived;
    }

    public function addSessionReceived(Session $session): static
    {
        if (!$this->sessionsReceived->contains($session)) {
            $this->sessionsReceived->add($session);
            $session->setToUser($this);
        }

        return $this;
    }

    public function removeSessionReceived(Session $session): static
    {
        if ($this->sessionsReceived->removeElement($session)) {
            // set the owning side to null (unless already changed)
            if ($session->getToUser() === $this) {
                $session->setToUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessagesSent(): Collection
    {
        return $this->messagesSent;
    }

    public function addMessageSent(Message $message): static
    {
        if (!$this->messagesSent->contains($message)) {
            $this->messagesSent->add($message);
            $message->setSender($this);
        }

        return $this;
    }

    public function removeMessageSent(Message $message): static
    {
        if ($this->messagesSent->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getSender() === $this) {
                $message->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessagesReceived(): Collection
    {
        return $this->messagesReceived;
    }

    public function addMessageReceived(Message $message): static
    {
        if (!$this->messagesReceived->contains($message)) {
            $this->messagesReceived->add($message);
            $message->setReceiver($this);
        }

        return $this;
    }

    public function removeMessageReceived(Message $message): static
    {
        if ($this->messagesReceived->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getReceiver() === $this) {
                $message->setReceiver(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviewsGiven(): Collection
    {
        return $this->reviewsGiven;
    }

    public function addReviewGiven(Review $review): static
    {
        if (!$this->reviewsGiven->contains($review)) {
            $this->reviewsGiven->add($review);
            $review->setReviewer($this);
        }

        return $this;
    }

    public function removeReviewGiven(Review $review): static
    {
        if ($this->reviewsGiven->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getReviewer() === $this) {
                $review->setReviewer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviewsReceived(): Collection
    {
        return $this->reviewsReceived;
    }

    public function addReviewReceived(Review $review): static
    {
        if (!$this->reviewsReceived->contains($review)) {
            $this->reviewsReceived->add($review);
            $review->setReviewedUser($this);
        }

        return $this;
    }

    public function removeReviewReceived(Review $review): static
    {
        if ($this->reviewsReceived->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getReviewedUser() === $this) {
                $review->setReviewedUser(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        if ($this->firstName && $this->lastName) {
            return $this->firstName . ' ' . $this->lastName;
        }
        
        return $this->username ?? 'Unknown User';
    }
} 
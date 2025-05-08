<?php

namespace App\Document;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    private ?string $id = null;
    private string $username;
    private string $email;
    private array $roles = [];
    private string $password;
    private ?string $firstName = null;
    private ?string $lastName = null;
    private ?string $avatar = null;
    private ?string $bio = null;
    private ?string $location = null;
    private array $skillsOffered = [];
    private array $skillsWanted = [];
    private array $availability = []; // Store available days and times
    private \DateTimeImmutable $createdAt;
    private ?\DateTimeImmutable $updatedAt = null;
    private ?\DateTimeImmutable $registeredAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
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
        return $this->email;
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

    public function setRoles(array $roles): self
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

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;
        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): self
    {
        $this->bio = $bio;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;
        return $this;
    }

    public function getSkillsOffered(): array
    {
        return $this->skillsOffered;
    }

    public function setSkillsOffered(array $skillsOffered): self
    {
        $this->skillsOffered = $skillsOffered;
        return $this;
    }

    public function addSkillOffered(string $skill): self
    {
        if (!in_array($skill, $this->skillsOffered)) {
            $this->skillsOffered[] = $skill;
        }
        return $this;
    }

    public function removeSkillOffered(string $skill): self
    {
        if (($key = array_search($skill, $this->skillsOffered)) !== false) {
            unset($this->skillsOffered[$key]);
            $this->skillsOffered = array_values($this->skillsOffered);
        }
        return $this;
    }

    public function getSkillsWanted(): array
    {
        return $this->skillsWanted;
    }

    public function setSkillsWanted(array $skillsWanted): self
    {
        $this->skillsWanted = $skillsWanted;
        return $this;
    }

    public function addSkillWanted(string $skill): self
    {
        if (!in_array($skill, $this->skillsWanted)) {
            $this->skillsWanted[] = $skill;
        }
        return $this;
    }

    public function removeSkillWanted(string $skill): self
    {
        if (($key = array_search($skill, $this->skillsWanted)) !== false) {
            unset($this->skillsWanted[$key]);
            $this->skillsWanted = array_values($this->skillsWanted);
        }
        return $this;
    }

    public function getAvailability(): array
    {
        return $this->availability;
    }

    public function setAvailability(array $availability): self
    {
        $this->availability = $availability;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getRegisteredAt(): ?\DateTimeImmutable
    {
        return $this->registeredAt;
    }

    public function setRegisteredAt(?\DateTimeImmutable $registeredAt): self
    {
        $this->registeredAt = $registeredAt;
        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }

    /**
     * Convert the User object to an array for MongoDB storage
     */
    public function toArray(): array
    {
        return [
            'username' => $this->username,
            'email' => $this->email,
            'roles' => $this->roles,
            'password' => $this->password,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'avatar' => $this->avatar,
            'bio' => $this->bio,
            'location' => $this->location,
            'skills_offered' => $this->skillsOffered,
            'skills_wanted' => $this->skillsWanted,
            'availability' => $this->availability,
            'created_at' => $this->createdAt->format('c'),
            'updated_at' => $this->updatedAt ? $this->updatedAt->format('c') : null,
            'registered_at' => $this->registeredAt ? $this->registeredAt->format('c') : null,
        ];
    }

    /**
     * Create a User object from a MongoDB document
     */
    public static function fromArray(array $data): self
    {
        $user = new self();
        
        if (isset($data['_id'])) {
            $user->setId((string) $data['_id']);
        }
        
        if (isset($data['username'])) {
            $user->setUsername($data['username']);
        }
        
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        
        if (isset($data['roles'])) {
            $user->setRoles($data['roles']);
        }
        
        if (isset($data['password'])) {
            $user->setPassword($data['password']);
        }
        
        if (isset($data['firstName'])) {
            $user->setFirstName($data['firstName']);
        }
        
        if (isset($data['lastName'])) {
            $user->setLastName($data['lastName']);
        }
        
        if (isset($data['avatar'])) {
            $user->setAvatar($data['avatar']);
        }
        
        if (isset($data['bio'])) {
            $user->setBio($data['bio']);
        }
        
        if (isset($data['location'])) {
            $user->setLocation($data['location']);
        }
        
        if (isset($data['skills_offered'])) {
            $user->setSkillsOffered($data['skills_offered']);
        }
        
        if (isset($data['skills_wanted'])) {
            $user->setSkillsWanted($data['skills_wanted']);
        }
        
        if (isset($data['availability'])) {
            $user->setAvailability($data['availability']);
        }
        
        if (isset($data['created_at'])) {
            try {
                $user->createdAt = new \DateTimeImmutable($data['created_at']);
            } catch (\Exception $e) {
                // Handle date parsing error
            }
        }
        
        if (isset($data['updated_at']) && $data['updated_at'] !== null) {
            try {
                $user->setUpdatedAt(new \DateTimeImmutable($data['updated_at']));
            } catch (\Exception $e) {
                // Handle date parsing error
            }
        }
        
        if (isset($data['registered_at']) && $data['registered_at'] !== null) {
            try {
                $user->setRegisteredAt(new \DateTimeImmutable($data['registered_at']));
            } catch (\Exception $e) {
                // Handle date parsing error
            }
        }
        
        return $user;
    }
} 
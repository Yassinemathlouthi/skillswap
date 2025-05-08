<?php

namespace App\Entity;

use App\Repository\SkillRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SkillRepository::class)]
class Skill
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'skill', targetEntity: UserSkillOffered::class, orphanRemoval: true)]
    private Collection $userSkillOffered;

    #[ORM\OneToMany(mappedBy: 'skill', targetEntity: UserSkillWanted::class, orphanRemoval: true)]
    private Collection $userSkillWanted;

    #[ORM\OneToMany(mappedBy: 'skill', targetEntity: SkillCategorySkill::class, orphanRemoval: true)]
    private Collection $skillCategories;

    public function __construct()
    {
        $this->userSkillOffered = new ArrayCollection();
        $this->userSkillWanted = new ArrayCollection();
        $this->skillCategories = new ArrayCollection();
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
     * @return Collection<int, UserSkillOffered>
     */
    public function getUserSkillOffered(): Collection
    {
        return $this->userSkillOffered;
    }

    public function addUserSkillOffered(UserSkillOffered $userSkillOffered): static
    {
        if (!$this->userSkillOffered->contains($userSkillOffered)) {
            $this->userSkillOffered->add($userSkillOffered);
            $userSkillOffered->setSkill($this);
        }

        return $this;
    }

    public function removeUserSkillOffered(UserSkillOffered $userSkillOffered): static
    {
        if ($this->userSkillOffered->removeElement($userSkillOffered)) {
            // set the owning side to null (unless already changed)
            if ($userSkillOffered->getSkill() === $this) {
                $userSkillOffered->setSkill(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserSkillWanted>
     */
    public function getUserSkillWanted(): Collection
    {
        return $this->userSkillWanted;
    }

    public function addUserSkillWanted(UserSkillWanted $userSkillWanted): static
    {
        if (!$this->userSkillWanted->contains($userSkillWanted)) {
            $this->userSkillWanted->add($userSkillWanted);
            $userSkillWanted->setSkill($this);
        }

        return $this;
    }

    public function removeUserSkillWanted(UserSkillWanted $userSkillWanted): static
    {
        if ($this->userSkillWanted->removeElement($userSkillWanted)) {
            // set the owning side to null (unless already changed)
            if ($userSkillWanted->getSkill() === $this) {
                $userSkillWanted->setSkill(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SkillCategorySkill>
     */
    public function getSkillCategories(): Collection
    {
        return $this->skillCategories;
    }

    public function addSkillCategory(SkillCategorySkill $skillCategory): static
    {
        if (!$this->skillCategories->contains($skillCategory)) {
            $this->skillCategories->add($skillCategory);
            $skillCategory->setSkill($this);
        }

        return $this;
    }

    public function removeSkillCategory(SkillCategorySkill $skillCategory): static
    {
        if ($this->skillCategories->removeElement($skillCategory)) {
            // set the owning side to null (unless already changed)
            if ($skillCategory->getSkill() === $this) {
                $skillCategory->setSkill(null);
            }
        }

        return $this;
    }
    
    public function __toString(): string
    {
        return $this->name ?? 'Unnamed Skill';
    }
} 
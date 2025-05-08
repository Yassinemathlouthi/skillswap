<?php

namespace App\Entity;

use App\Repository\SkillCategorySkillRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SkillCategorySkillRepository::class)]
#[ORM\Table(name: 'skill_category_skill')]
class SkillCategorySkill
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'skills')]
    #[ORM\JoinColumn(nullable: false, name: 'category_id')]
    private ?SkillCategory $category = null;

    #[ORM\ManyToOne(inversedBy: 'skillCategories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Skill $skill = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): ?SkillCategory
    {
        return $this->category;
    }

    public function setCategory(?SkillCategory $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getSkill(): ?Skill
    {
        return $this->skill;
    }

    public function setSkill(?Skill $skill): static
    {
        $this->skill = $skill;

        return $this;
    }
    
    public function __toString(): string
    {
        return $this->skill ? $this->skill->getName() : 'Unnamed Skill';
    }
} 
<?php

namespace App\Document;

class SkillCategory
{
    private ?string $id = null;
    private string $name;
    private ?string $description = null;
    private ?string $icon = null;
    private array $skills = []; // List of skills in this category
    private \DateTimeImmutable $createdAt;
    private ?\DateTimeImmutable $updatedAt = null;

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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    public function getSkills(): array
    {
        return $this->skills;
    }

    public function setSkills(array $skills): self
    {
        $this->skills = $skills;
        return $this;
    }

    public function addSkill(string $skill): self
    {
        if (!in_array($skill, $this->skills)) {
            $this->skills[] = $skill;
        }
        return $this;
    }

    public function removeSkill(string $skill): self
    {
        if (($key = array_search($skill, $this->skills)) !== false) {
            unset($this->skills[$key]);
            $this->skills = array_values($this->skills);
        }
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

    /**
     * Convert the SkillCategory object to an array for MongoDB storage
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'icon' => $this->icon,
            'skills' => $this->skills,
            'created_at' => $this->createdAt->format('c'),
            'updated_at' => $this->updatedAt ? $this->updatedAt->format('c') : null,
        ];
    }

    /**
     * Create a SkillCategory object from a MongoDB document
     */
    public static function fromArray(array $data): self
    {
        $category = new self();
        
        if (isset($data['_id'])) {
            $category->setId((string) $data['_id']);
        }
        
        if (isset($data['name'])) {
            $category->setName($data['name']);
        }
        
        if (isset($data['description'])) {
            $category->setDescription($data['description']);
        }
        
        if (isset($data['icon'])) {
            $category->setIcon($data['icon']);
        }
        
        if (isset($data['skills']) && is_array($data['skills'])) {
            $category->setSkills($data['skills']);
        }
        
        if (isset($data['created_at'])) {
            try {
                $category->createdAt = new \DateTimeImmutable($data['created_at']);
            } catch (\Exception $e) {
                // Handle date parsing error
            }
        }
        
        if (isset($data['updated_at']) && $data['updated_at'] !== null) {
            try {
                $category->setUpdatedAt(new \DateTimeImmutable($data['updated_at']));
            } catch (\Exception $e) {
                // Handle date parsing error
            }
        }
        
        return $category;
    }
} 
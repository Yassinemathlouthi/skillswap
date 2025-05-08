<?php

namespace App\Document;

class Session
{
    private ?string $id = null;
    private string $fromUserId;
    private string $toUserId;
    private \DateTimeImmutable $dateTime;
    private string $status; // pending, confirmed, canceled, completed
    private ?string $notes = null;
    private ?string $skill = null; // Which skill is being shared in this session
    private \DateTimeImmutable $createdAt;
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = 'pending';
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

    public function getFromUserId(): string
    {
        return $this->fromUserId;
    }

    public function setFromUserId(string $fromUserId): self
    {
        $this->fromUserId = $fromUserId;
        return $this;
    }

    public function getToUserId(): string
    {
        return $this->toUserId;
    }

    public function setToUserId(string $toUserId): self
    {
        $this->toUserId = $toUserId;
        return $this;
    }

    public function getDateTime(): \DateTimeImmutable
    {
        return $this->dateTime;
    }

    public function setDateTime(\DateTimeImmutable $dateTime): self
    {
        $this->dateTime = $dateTime;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $allowedStatuses = ['pending', 'confirmed', 'canceled', 'completed'];
        if (!in_array($status, $allowedStatuses)) {
            throw new \InvalidArgumentException('Invalid status');
        }
        $this->status = $status;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    public function getSkill(): ?string
    {
        return $this->skill;
    }

    public function setSkill(?string $skill): self
    {
        $this->skill = $skill;
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
     * Convert the Session object to an array for MongoDB storage
     */
    public function toArray(): array
    {
        return [
            'from_user_id' => $this->fromUserId,
            'to_user_id' => $this->toUserId,
            'date_time' => $this->dateTime->format('c'),
            'status' => $this->status,
            'notes' => $this->notes,
            'skill' => $this->skill,
            'created_at' => $this->createdAt->format('c'),
            'updated_at' => $this->updatedAt ? $this->updatedAt->format('c') : null,
        ];
    }

    /**
     * Create a Session object from a MongoDB document
     */
    public static function fromArray(array $data): self
    {
        $session = new self();
        
        if (isset($data['_id'])) {
            $session->setId((string) $data['_id']);
        }
        
        if (isset($data['from_user_id'])) {
            $session->setFromUserId($data['from_user_id']);
        }
        
        if (isset($data['to_user_id'])) {
            $session->setToUserId($data['to_user_id']);
        }
        
        if (isset($data['date_time'])) {
            try {
                $session->setDateTime(new \DateTimeImmutable($data['date_time']));
            } catch (\Exception $e) {
                // Handle date parsing error
            }
        }
        
        if (isset($data['status'])) {
            $session->setStatus($data['status']);
        }
        
        if (isset($data['notes'])) {
            $session->setNotes($data['notes']);
        }
        
        if (isset($data['skill'])) {
            $session->setSkill($data['skill']);
        }
        
        if (isset($data['created_at'])) {
            try {
                $session->createdAt = new \DateTimeImmutable($data['created_at']);
            } catch (\Exception $e) {
                // Handle date parsing error
            }
        }
        
        if (isset($data['updated_at']) && $data['updated_at'] !== null) {
            try {
                $session->setUpdatedAt(new \DateTimeImmutable($data['updated_at']));
            } catch (\Exception $e) {
                // Handle date parsing error
            }
        }
        
        return $session;
    }
} 
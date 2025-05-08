<?php

namespace App\Document;

class Message
{
    private ?string $id = null;
    private string $senderId;
    private string $receiverId;
    private string $content;
    private \DateTimeImmutable $timestamp;
    private bool $isRead = false;

    public function __construct()
    {
        $this->timestamp = new \DateTimeImmutable();
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

    public function getSenderId(): string
    {
        return $this->senderId;
    }

    public function setSenderId(string $senderId): self
    {
        $this->senderId = $senderId;
        return $this;
    }

    public function getReceiverId(): string
    {
        return $this->receiverId;
    }

    public function setReceiverId(string $receiverId): self
    {
        $this->receiverId = $receiverId;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getTimestamp(): \DateTimeImmutable
    {
        return $this->timestamp;
    }

    public function setTimestamp(\DateTimeImmutable $timestamp): self
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    public function isRead(): bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): self
    {
        $this->isRead = $isRead;
        return $this;
    }

    public function markAsRead(): self
    {
        $this->isRead = true;
        return $this;
    }

    /**
     * Convert the Message object to an array for MongoDB storage
     */
    public function toArray(): array
    {
        return [
            'sender_id' => $this->senderId,
            'receiver_id' => $this->receiverId,
            'content' => $this->content,
            'timestamp' => $this->timestamp->format('c'),
            'is_read' => $this->isRead,
        ];
    }

    /**
     * Create a Message object from a MongoDB document
     */
    public static function fromArray(array $data): self
    {
        $message = new self();
        
        if (isset($data['_id'])) {
            $message->setId((string) $data['_id']);
        }
        
        if (isset($data['sender_id'])) {
            $message->setSenderId($data['sender_id']);
        }
        
        if (isset($data['receiver_id'])) {
            $message->setReceiverId($data['receiver_id']);
        }
        
        if (isset($data['content'])) {
            $message->setContent($data['content']);
        }
        
        if (isset($data['timestamp'])) {
            try {
                $message->setTimestamp(new \DateTimeImmutable($data['timestamp']));
            } catch (\Exception $e) {
                // Handle date parsing error
            }
        }
        
        if (isset($data['is_read'])) {
            $message->setIsRead((bool) $data['is_read']);
        }
        
        return $message;
    }
} 
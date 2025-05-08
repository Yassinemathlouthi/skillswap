<?php

namespace App\Document;

class Review
{
    private ?string $id = null;
    private string $reviewerId;
    private string $reviewedUserId;
    private string $sessionId;
    private int $rating; // 1-5 stars
    private ?string $comment = null;
    private \DateTimeImmutable $createdAt;

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

    public function getReviewerId(): string
    {
        return $this->reviewerId;
    }

    public function setReviewerId(string $reviewerId): self
    {
        $this->reviewerId = $reviewerId;
        return $this;
    }

    public function getReviewedUserId(): string
    {
        return $this->reviewedUserId;
    }

    public function setReviewedUserId(string $reviewedUserId): self
    {
        $this->reviewedUserId = $reviewedUserId;
        return $this;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function setSessionId(string $sessionId): self
    {
        $this->sessionId = $sessionId;
        return $this;
    }

    public function getRating(): int
    {
        return $this->rating;
    }

    public function setRating(int $rating): self
    {
        // Ensure the rating is between 1 and 5
        if ($rating < 1 || $rating > 5) {
            throw new \InvalidArgumentException('Rating must be between 1 and 5');
        }
        $this->rating = $rating;
        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Convert the Review object to an array for MongoDB storage
     */
    public function toArray(): array
    {
        return [
            'reviewer_id' => $this->reviewerId,
            'reviewed_user_id' => $this->reviewedUserId,
            'session_id' => $this->sessionId,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'created_at' => $this->createdAt->format('c'),
        ];
    }

    /**
     * Create a Review object from a MongoDB document
     */
    public static function fromArray(array $data): self
    {
        $review = new self();
        
        if (isset($data['_id'])) {
            $review->setId((string) $data['_id']);
        }
        
        if (isset($data['reviewer_id'])) {
            $review->setReviewerId($data['reviewer_id']);
        }
        
        if (isset($data['reviewed_user_id'])) {
            $review->setReviewedUserId($data['reviewed_user_id']);
        }
        
        if (isset($data['session_id'])) {
            $review->setSessionId($data['session_id']);
        }
        
        if (isset($data['rating'])) {
            $review->setRating((int) $data['rating']);
        }
        
        if (isset($data['comment'])) {
            $review->setComment($data['comment']);
        }
        
        if (isset($data['created_at'])) {
            try {
                $review->createdAt = new \DateTimeImmutable($data['created_at']);
            } catch (\Exception $e) {
                // Handle date parsing error
            }
        }
        
        return $review;
    }
} 
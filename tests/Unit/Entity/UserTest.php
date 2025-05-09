<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use App\Entity\UserAvailability;
use App\Entity\UserSkillOffered;
use App\Entity\UserSkillWanted;
use App\Entity\Review;
use App\Entity\Message;
use App\Entity\Session;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User();
    }

    public function testUserInitialization(): void
    {
        // Test that a new user has default values
        $this->assertInstanceOf(\DateTimeInterface::class, $this->user->getCreatedAt());
        $this->assertEmpty($this->user->getSkillsOffered());
        $this->assertEmpty($this->user->getSkillsWanted());
        $this->assertEmpty($this->user->getAvailabilities());
        $this->assertContains('ROLE_USER', $this->user->getRoles());
    }

    public function testUserIdentifiers(): void
    {
        // Test user identifier methods
        $email = 'test@example.com';
        $username = 'testuser';
        
        $this->user->setEmail($email);
        $this->user->setUsername($username);
        
        $this->assertEquals($email, $this->user->getEmail());
        $this->assertEquals($username, $this->user->getUsername());
        $this->assertEquals($email, $this->user->getUserIdentifier());
    }

    public function testUserProfile(): void
    {
        // Test profile-related methods
        $firstName = 'John';
        $lastName = 'Doe';
        $bio = 'Test bio';
        $location = 'Test City';
        $avatar = 'avatar.jpg';
        
        $this->user->setFirstName($firstName);
        $this->user->setLastName($lastName);
        $this->user->setBio($bio);
        $this->user->setLocation($location);
        $this->user->setAvatar($avatar);
        
        $this->assertEquals($firstName, $this->user->getFirstName());
        $this->assertEquals($lastName, $this->user->getLastName());
        $this->assertEquals($bio, $this->user->getBio());
        $this->assertEquals($location, $this->user->getLocation());
        $this->assertEquals($avatar, $this->user->getAvatar());
    }

    public function testUserRoles(): void
    {
        // Test roles methods
        $roles = ['ROLE_ADMIN'];
        $this->user->setRoles($roles);
        
        $userRoles = $this->user->getRoles();
        $this->assertContains('ROLE_USER', $userRoles);
        $this->assertContains('ROLE_ADMIN', $userRoles);
        $this->assertCount(2, $userRoles);
    }

    public function testUserGeolocation(): void
    {
        // Test geolocation fields
        $latitude = 42.3601;
        $longitude = -71.0589;
        
        $this->user->setLatitude($latitude);
        $this->user->setLongitude($longitude);
        
        $this->assertEquals($latitude, $this->user->getLatitude());
        $this->assertEquals($longitude, $this->user->getLongitude());
    }

    public function testUserAvailability(): void
    {
        // Test adding/removing availabilities
        $availability = $this->createMock(UserAvailability::class);
        $availability->method('getUser')->willReturn($this->user);
        
        $this->user->addAvailability($availability);
        $this->assertCount(1, $this->user->getAvailabilities());
        
        $this->user->removeAvailability($availability);
        $this->assertCount(0, $this->user->getAvailabilities());
    }

    public function testUserSkills(): void
    {
        // Test adding/removing skills offered and wanted
        $skillOffered = $this->createMock(UserSkillOffered::class);
        $skillOffered->method('getUser')->willReturn($this->user);
        
        $skillWanted = $this->createMock(UserSkillWanted::class);
        $skillWanted->method('getUser')->willReturn($this->user);
        
        $this->user->addSkillOffered($skillOffered);
        $this->user->addSkillWanted($skillWanted);
        
        $this->assertCount(1, $this->user->getSkillsOffered());
        $this->assertCount(1, $this->user->getSkillsWanted());
        
        $this->user->removeSkillOffered($skillOffered);
        $this->user->removeSkillWanted($skillWanted);
        
        $this->assertCount(0, $this->user->getSkillsOffered());
        $this->assertCount(0, $this->user->getSkillsWanted());
    }

    public function testSessions(): void
    {
        // Test sessions requested and received
        $sessionRequested = $this->createMock(Session::class);
        $sessionRequested->method('getFromUser')->willReturn($this->user);
        
        $sessionReceived = $this->createMock(Session::class);
        $sessionReceived->method('getToUser')->willReturn($this->user);
        
        $this->user->addSessionRequested($sessionRequested);
        $this->user->addSessionReceived($sessionReceived);
        
        $this->assertCount(1, $this->user->getSessionsRequested());
        $this->assertCount(1, $this->user->getSessionsReceived());
        
        $this->user->removeSessionRequested($sessionRequested);
        $this->user->removeSessionReceived($sessionReceived);
        
        $this->assertCount(0, $this->user->getSessionsRequested());
        $this->assertCount(0, $this->user->getSessionsReceived());
    }

    public function testMessages(): void
    {
        // Test messages sent and received
        $messageSent = $this->createMock(Message::class);
        $messageSent->method('getSender')->willReturn($this->user);
        
        $messageReceived = $this->createMock(Message::class);
        $messageReceived->method('getReceiver')->willReturn($this->user);
        
        $this->user->addMessageSent($messageSent);
        $this->user->addMessageReceived($messageReceived);
        
        $this->assertCount(1, $this->user->getMessagesSent());
        $this->assertCount(1, $this->user->getMessagesReceived());
        
        $this->user->removeMessageSent($messageSent);
        $this->user->removeMessageReceived($messageReceived);
        
        $this->assertCount(0, $this->user->getMessagesSent());
        $this->assertCount(0, $this->user->getMessagesReceived());
    }

    public function testReviews(): void
    {
        // Test reviews given and received
        $reviewGiven = $this->createMock(Review::class);
        $reviewGiven->method('getReviewer')->willReturn($this->user);
        
        $reviewReceived = $this->createMock(Review::class);
        $reviewReceived->method('getReviewedUser')->willReturn($this->user);
        
        $this->user->addReviewGiven($reviewGiven);
        $this->user->addReviewReceived($reviewReceived);
        
        $this->assertCount(1, $this->user->getReviewsGiven());
        $this->assertCount(1, $this->user->getReviewsReceived());
        
        $this->user->removeReviewGiven($reviewGiven);
        $this->user->removeReviewReceived($reviewReceived);
        
        $this->assertCount(0, $this->user->getReviewsGiven());
        $this->assertCount(0, $this->user->getReviewsReceived());
    }

    public function testPasswordMethods(): void
    {
        // Test password-related methods
        $password = 'securepassword';
        $this->user->setPassword($password);
        
        $this->assertEquals($password, $this->user->getPassword());
        
        // Test eraseCredentials doesn't crash
        $this->user->eraseCredentials();
    }
} 
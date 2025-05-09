<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Session;
use App\Entity\User;
use App\Entity\Skill;
use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
    private Session $session;
    private User $fromUser;
    private User $toUser;

    protected function setUp(): void
    {
        $this->session = new Session();
        $this->fromUser = $this->createMock(User::class);
        $this->toUser = $this->createMock(User::class);
    }

    public function testSessionInitialization(): void
    {
        // Test that a new session has default values
        $this->assertInstanceOf(\DateTimeInterface::class, $this->session->getCreatedAt());
        $this->assertEquals(Session::STATUS_PENDING, $this->session->getStatus());
        $this->assertEquals(60, $this->session->getDuration()); // Default duration
        $this->assertEquals(30, $this->session->getReminderMinutesBefore()); // Default reminder
        $this->assertFalse($this->session->isReminderSent());
    }

    public function testSessionUsers(): void
    {
        // Test setting and getting from/to users
        $this->session->setFromUser($this->fromUser);
        $this->session->setToUser($this->toUser);
        
        $this->assertSame($this->fromUser, $this->session->getFromUser());
        $this->assertSame($this->toUser, $this->session->getToUser());
    }

    public function testSessionDateTime(): void
    {
        // Test setting and getting date/time
        $dateTime = new \DateTime('+2 days');
        $this->session->setDateTime($dateTime);
        
        $this->assertSame($dateTime, $this->session->getDateTime());
    }

    public function testSessionDuration(): void
    {
        // Test setting and getting duration
        $duration = 90; // 90 minutes
        $this->session->setDuration($duration);
        
        $this->assertEquals($duration, $this->session->getDuration());
    }

    public function testSessionLocation(): void
    {
        // Test location related properties
        $location = 'Coffee Shop';
        $latitude = 42.3601;
        $longitude = -71.0589;
        
        $this->session->setLocation($location);
        $this->session->setLatitude($latitude);
        $this->session->setLongitude($longitude);
        
        $this->assertEquals($location, $this->session->getLocation());
        $this->assertEquals($latitude, $this->session->getLatitude());
        $this->assertEquals($longitude, $this->session->getLongitude());
    }

    public function testSessionReminder(): void
    {
        // Test reminder properties
        $reminderMinutes = 60;
        $reminderSent = true;
        
        $this->session->setReminderMinutesBefore($reminderMinutes);
        $this->session->setReminderSent($reminderSent);
        
        $this->assertEquals($reminderMinutes, $this->session->getReminderMinutesBefore());
        $this->assertTrue($this->session->isReminderSent());
    }

    public function testSessionStatus(): void
    {
        // Test status transitions
        $this->assertEquals(Session::STATUS_PENDING, $this->session->getStatus());
        
        $this->session->setStatus(Session::STATUS_CONFIRMED);
        $this->assertEquals(Session::STATUS_CONFIRMED, $this->session->getStatus());
        
        $this->session->setStatus(Session::STATUS_CANCELED);
        $this->assertEquals(Session::STATUS_CANCELED, $this->session->getStatus());
        
        $this->session->setStatus(Session::STATUS_COMPLETED);
        $this->assertEquals(Session::STATUS_COMPLETED, $this->session->getStatus());
    }

    public function testSessionNotes(): void
    {
        // Test session notes
        $notes = 'Meeting to discuss JavaScript basics';
        $this->session->setNotes($notes);
        
        $this->assertEquals($notes, $this->session->getNotes());
    }

    public function testSessionSkill(): void
    {
        // Test setting and getting the associated skill
        $skill = $this->createMock(Skill::class);
        $this->session->setSkill($skill);
        
        $this->assertSame($skill, $this->session->getSkill());
    }

    public function testSessionCalendarEvent(): void
    {
        // Test calendar event ID
        $calendarEventId = 'cal123456789';
        $this->session->setCalendarEventId($calendarEventId);
        
        $this->assertEquals($calendarEventId, $this->session->getCalendarEventId());
    }

    public function testSessionEndTime(): void
    {
        // Test end time calculation
        $startTime = new \DateTime('2023-01-01 10:00:00');
        $this->session->setDateTime($startTime);
        $this->session->setDuration(60); // 1 hour
        
        $endTime = $this->session->getEndTime();
        $this->assertInstanceOf(\DateTimeInterface::class, $endTime);
        
        // End time should be start time + duration
        $expectedEndTime = clone $startTime;
        $expectedEndTime->modify('+60 minutes');
        
        $this->assertEquals($expectedEndTime->format('Y-m-d H:i:s'), $endTime->format('Y-m-d H:i:s'));
    }

    public function testSessionIsUpcoming(): void
    {
        // Test isUpcoming method
        // Set date in future
        $futureDate = new \DateTime('+2 days');
        $this->session->setDateTime($futureDate);
        $this->assertTrue($this->session->isUpcoming());
        
        // Set date in past
        $pastDate = new \DateTime('-2 days');
        $this->session->setDateTime($pastDate);
        $this->assertFalse($this->session->isUpcoming());
    }
} 
<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Message;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    private Message $message;
    private User $sender;
    private User $receiver;
    
    protected function setUp(): void
    {
        $this->message = new Message();
        $this->sender = $this->createMock(User::class);
        $this->receiver = $this->createMock(User::class);
    }
    
    public function testMessageInitialization(): void
    {
        // Test that a new message has default values
        $this->assertInstanceOf(\DateTimeInterface::class, $this->message->getTimestamp());
        $this->assertFalse($this->message->isIsRead());
    }
    
    public function testMessageSender(): void
    {
        // Test setting and getting sender
        $this->message->setSender($this->sender);
        $this->assertSame($this->sender, $this->message->getSender());
    }
    
    public function testMessageReceiver(): void
    {
        // Test setting and getting receiver
        $this->message->setReceiver($this->receiver);
        $this->assertSame($this->receiver, $this->message->getReceiver());
    }
    
    public function testMessageContent(): void
    {
        // Test setting and getting message content
        $content = 'Test message content';
        $this->message->setContent($content);
        $this->assertEquals($content, $this->message->getContent());
    }
    
    public function testMessageTimestamp(): void
    {
        // Test setting and getting timestamp
        $timestamp = new \DateTime('2023-01-01 12:00:00');
        $this->message->setTimestamp($timestamp);
        $this->assertSame($timestamp, $this->message->getTimestamp());
    }
    
    public function testMessageReadStatus(): void
    {
        // Test setting and getting read status
        $this->assertFalse($this->message->isIsRead()); // Default is false
        
        $this->message->setIsRead(true);
        $this->assertTrue($this->message->isIsRead());
        
        $this->message->setIsRead(false);
        $this->assertFalse($this->message->isIsRead());
    }
} 
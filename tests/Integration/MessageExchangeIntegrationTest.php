<?php

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use App\Entity\Message;
use App\Repository\UserRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;

class MessageExchangeIntegrationTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $userRepository;
    private $messageRepository;
    private $sender;
    private $receiver;
    
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->userRepository = static::getContainer()->get(UserRepository::class);
        $this->messageRepository = static::getContainer()->get(MessageRepository::class);
        
        // Create test users for messaging
        $this->setupTestUsers();
    }
    
    private function setupTestUsers(): void
    {
        // First test user (message sender)
        $sender = $this->userRepository->findOneByEmail('message.sender@example.com');
        if (!$sender) {
            $sender = new User();
            $sender->setUsername('message_sender');
            $sender->setEmail('message.sender@example.com');
            $sender->setPassword('$2y$13$HOg6QQakJrfpPjSP6oGfYeRj5Nw9w5gQhEuP4Cxw4FvILQcFE09e.'); // 'password123'
            $sender->setFirstName('Message');
            $sender->setLastName('Sender');
            
            $this->entityManager->persist($sender);
            $this->entityManager->flush();
        }
        $this->sender = $sender;
        
        // Second test user (message receiver)
        $receiver = $this->userRepository->findOneByEmail('message.receiver@example.com');
        if (!$receiver) {
            $receiver = new User();
            $receiver->setUsername('message_receiver');
            $receiver->setEmail('message.receiver@example.com');
            $receiver->setPassword('$2y$13$HOg6QQakJrfpPjSP6oGfYeRj5Nw9w5gQhEuP4Cxw4FvILQcFE09e.'); // 'password123'
            $receiver->setFirstName('Message');
            $receiver->setLastName('Receiver');
            
            $this->entityManager->persist($receiver);
            $this->entityManager->flush();
        }
        $this->receiver = $receiver;
    }
    
    public function testSendingAndReceivingMessages(): void
    {
        // Login as the sender
        $this->client->loginUser($this->sender);
        
        // 1. Go to the receiver's profile
        $crawler = $this->client->request('GET', '/profile/' . $this->receiver->getId());
        $this->assertResponseIsSuccessful();
        
        // 2. Click on "Send Message" and go to message form
        $link = $crawler->selectLink('Send Message')->link();
        $crawler = $this->client->click($link);
        $this->assertResponseIsSuccessful();
        
        // 3. Compose and send a message
        $testMessage = 'This is a test message ' . uniqid();
        $form = $crawler->selectButton('Send')->form([
            'message_form[content]' => $testMessage
        ]);
        
        $this->client->submit($form);
        
        // 4. Verify that we're redirected after sending
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        
        // 5. Verify that the message exists in the database
        $message = $this->messageRepository->findOneBy([
            'sender' => $this->sender,
            'receiver' => $this->receiver,
            'content' => $testMessage
        ]);
        
        $this->assertNotNull($message, 'Message should be saved in the database');
        $this->assertFalse($message->isIsRead(), 'Message should be marked as unread initially');
        
        // 6. Logout and login as the receiver
        $this->client->request('GET', '/logout');
        $this->client->loginUser($this->receiver);
        
        // 7. Go to the messages inbox
        $crawler = $this->client->request('GET', '/messages');
        $this->assertResponseIsSuccessful();
        
        // 8. Verify the message is visible in the inbox
        $this->assertStringContainsString($testMessage, $this->client->getResponse()->getContent());
        
        // 9. View the conversation with the sender
        $link = $crawler->selectLink($this->sender->getUsername())->link();
        $crawler = $this->client->click($link);
        $this->assertResponseIsSuccessful();
        
        // 10. Verify the message is visible in the conversation
        $this->assertStringContainsString($testMessage, $this->client->getResponse()->getContent());
        
        // 11. Refresh the message entity and verify it's now marked as read
        $this->entityManager->refresh($message);
        $this->assertTrue($message->isIsRead(), 'Message should be marked as read after viewing');
        
        // 12. Reply to the message
        $replyText = 'This is a reply to your message ' . uniqid();
        $form = $crawler->selectButton('Send')->form([
            'message_form[content]' => $replyText
        ]);
        
        $this->client->submit($form);
        
        // 13. Verify that the reply was saved
        $reply = $this->messageRepository->findOneBy([
            'sender' => $this->receiver,
            'receiver' => $this->sender,
            'content' => $replyText
        ]);
        
        $this->assertNotNull($reply, 'Reply should be saved in the database');
        $this->assertFalse($reply->isIsRead(), 'Reply should be marked as unread');
        
        // 14. Go back to the conversation and verify both messages are displayed
        $this->client->request('GET', '/messages/' . $this->sender->getId());
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString($testMessage, $this->client->getResponse()->getContent());
        $this->assertStringContainsString($replyText, $this->client->getResponse()->getContent());
    }
    
    public function testMessageNotification(): void
    {
        // 1. Create a message directly in the database
        $notificationMessage = new Message();
        $notificationMessage->setSender($this->sender);
        $notificationMessage->setReceiver($this->receiver);
        $notificationMessage->setContent('Notification test message ' . uniqid());
        $notificationMessage->setIsRead(false);
        
        $this->entityManager->persist($notificationMessage);
        $this->entityManager->flush();
        
        // 2. Login as the receiver
        $this->client->loginUser($this->receiver);
        
        // 3. Go to the homepage
        $crawler = $this->client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        
        // 4. Check for notification indicator (this will depend on your UI)
        $this->assertStringContainsString('unread-message', $this->client->getResponse()->getContent());
        
        // 5. Go to messages and mark as read
        $this->client->request('GET', '/messages/' . $this->sender->getId());
        $this->assertResponseIsSuccessful();
        
        // 6. Refresh message and check that it's now read
        $this->entityManager->refresh($notificationMessage);
        $this->assertTrue($notificationMessage->isIsRead(), 'Message should be marked as read');
        
        // 7. Check that notification is gone from homepage
        $crawler = $this->client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        $this->assertStringNotContainsString('unread-message', $this->client->getResponse()->getContent());
    }
    
    protected function tearDown(): void
    {
        // Clean up test messages
        $testMessages = $this->messageRepository->findBy([
            'sender' => [$this->sender, $this->receiver],
            'receiver' => [$this->sender, $this->receiver]
        ]);
        
        foreach ($testMessages as $message) {
            $this->entityManager->remove($message);
        }
        $this->entityManager->flush();
        
        parent::tearDown();
    }
} 
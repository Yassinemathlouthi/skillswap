<?php

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use App\Entity\Session;
use App\Entity\Skill;
use App\Repository\UserRepository;
use App\Repository\SessionRepository;
use App\Repository\SkillRepository;
use Doctrine\ORM\EntityManagerInterface;

class SessionBookingIntegrationTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $userRepository;
    private $sessionRepository;
    private $skillRepository;
    private $user1;
    private $user2;
    private $skill;
    
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->userRepository = static::getContainer()->get(UserRepository::class);
        $this->sessionRepository = static::getContainer()->get(SessionRepository::class);
        $this->skillRepository = static::getContainer()->get(SkillRepository::class);
        
        // Create test users if they don't exist
        $this->setupTestUsers();
        
        // Create test skill if needed
        $this->setupTestSkill();
    }
    
    private function setupTestUsers(): void
    {
        // First test user (session requester)
        $user1 = $this->userRepository->findOneByEmail('test.requester@example.com');
        if (!$user1) {
            $user1 = new User();
            $user1->setUsername('test_requester');
            $user1->setEmail('test.requester@example.com');
            $user1->setPassword('$2y$13$HOg6QQakJrfpPjSP6oGfYeRj5Nw9w5gQhEuP4Cxw4FvILQcFE09e.'); // 'password123'
            $user1->setFirstName('Test');
            $user1->setLastName('Requester');
            
            $this->entityManager->persist($user1);
            $this->entityManager->flush();
        }
        $this->user1 = $user1;
        
        // Second test user (session provider)
        $user2 = $this->userRepository->findOneByEmail('test.provider@example.com');
        if (!$user2) {
            $user2 = new User();
            $user2->setUsername('test_provider');
            $user2->setEmail('test.provider@example.com');
            $user2->setPassword('$2y$13$HOg6QQakJrfpPjSP6oGfYeRj5Nw9w5gQhEuP4Cxw4FvILQcFE09e.'); // 'password123'
            $user2->setFirstName('Test');
            $user2->setLastName('Provider');
            
            $this->entityManager->persist($user2);
            $this->entityManager->flush();
        }
        $this->user2 = $user2;
    }
    
    private function setupTestSkill(): void
    {
        $skill = $this->skillRepository->findOneBy(['name' => 'Test Skill']);
        if (!$skill) {
            $skill = new Skill();
            $skill->setName('Test Skill');
            $skill->setDescription('A skill used for testing');
            
            $this->entityManager->persist($skill);
            $this->entityManager->flush();
        }
        $this->skill = $skill;
    }
    
    public function testCompleteSessionBookingFlow(): void
    {
        // 1. Login as the requester
        $this->client->loginUser($this->user1);
        
        // 2. Go to the user profile of the provider
        $crawler = $this->client->request('GET', '/profile/' . $this->user2->getId());
        $this->assertResponseIsSuccessful();
        
        // 3. Submit a session request form
        $form = $crawler->selectButton('Request Session')->form([
            'session_request[dateTime]' => (new \DateTime('+2 days'))->format('Y-m-d H:i'),
            'session_request[skill]' => $this->skill->getId(),
            'session_request[duration]' => 60,
            'session_request[location]' => 'Coffee Shop',
            'session_request[notes]' => 'Test session booking note'
        ]);
        
        $this->client->submit($form);
        
        // 4. Verify redirection after session request
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        
        // 5. Find the newly created session in the database
        $session = $this->sessionRepository->findOneBy([
            'fromUser' => $this->user1,
            'toUser' => $this->user2,
            'status' => Session::STATUS_PENDING
        ], ['createdAt' => 'DESC']);
        
        $this->assertNotNull($session, 'Session should be created in the database');
        $this->assertEquals('Test session booking note', $session->getNotes());
        $this->assertEquals($this->skill->getId(), $session->getSkill()->getId());
        $this->assertEquals('Coffee Shop', $session->getLocation());
        $this->assertEquals(60, $session->getDuration());
        
        // 6. Logout requester and login as provider
        $this->client->request('GET', '/logout');
        $this->client->loginUser($this->user2);
        
        // 7. Go to sessions management page
        $crawler = $this->client->request('GET', '/sessions/received');
        $this->assertResponseIsSuccessful();
        
        // 8. Verify the session request is listed
        $this->assertStringContainsString('Test session booking note', $this->client->getResponse()->getContent());
        $this->assertStringContainsString($this->user1->getUsername(), $this->client->getResponse()->getContent());
        
        // 9. Confirm the session
        $this->client->request('GET', '/session/' . $session->getId() . '/confirm');
        
        // 10. Verify redirection after confirmation
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        
        // 11. Verify the session status is updated in the database
        $this->entityManager->refresh($session);
        $this->assertEquals(Session::STATUS_CONFIRMED, $session->getStatus());
        
        // 12. Logout and login back as requester
        $this->client->request('GET', '/logout');
        $this->client->loginUser($this->user1);
        
        // 13. Go to upcoming sessions page
        $crawler = $this->client->request('GET', '/sessions/requested');
        $this->assertResponseIsSuccessful();
        
        // 14. Verify that the session is listed as confirmed
        $this->assertStringContainsString('confirmed', $this->client->getResponse()->getContent());
        $this->assertStringContainsString($this->user2->getUsername(), $this->client->getResponse()->getContent());
    }
    
    public function testSessionCancellation(): void
    {
        // 1. Create a session directly
        $session = new Session();
        $session->setFromUser($this->user1);
        $session->setToUser($this->user2);
        $session->setDateTime(new \DateTime('+3 days'));
        $session->setDuration(90);
        $session->setSkill($this->skill);
        $session->setLocation('Library');
        $session->setNotes('Session to be canceled');
        $session->setStatus(Session::STATUS_CONFIRMED);
        
        $this->entityManager->persist($session);
        $this->entityManager->flush();
        
        // 2. Login as the requester
        $this->client->loginUser($this->user1);
        
        // 3. Cancel the session
        $this->client->request('GET', '/session/' . $session->getId() . '/cancel');
        
        // 4. Verify redirection after cancellation
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        
        // 5. Verify the session status is updated in the database
        $this->entityManager->refresh($session);
        $this->assertEquals(Session::STATUS_CANCELED, $session->getStatus());
        
        // 6. Go to sessions page and verify it shows as canceled
        $crawler = $this->client->request('GET', '/sessions/requested');
        $this->assertResponseIsSuccessful();
        
        // 7. Verify that the session is listed as canceled
        $this->assertStringContainsString('canceled', $this->client->getResponse()->getContent());
    }
    
    public function testSessionCompletion(): void
    {
        // 1. Create a session that's in the past
        $session = new Session();
        $session->setFromUser($this->user1);
        $session->setToUser($this->user2);
        $session->setDateTime(new \DateTime('-1 day')); // Past session
        $session->setDuration(60);
        $session->setSkill($this->skill);
        $session->setLocation('Online');
        $session->setNotes('Past session to be marked as completed');
        $session->setStatus(Session::STATUS_CONFIRMED);
        
        $this->entityManager->persist($session);
        $this->entityManager->flush();
        
        // 2. Login as provider
        $this->client->loginUser($this->user2);
        
        // 3. Mark the session as completed
        $this->client->request('GET', '/session/' . $session->getId() . '/complete');
        
        // 4. Verify redirection after completion
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        
        // 5. Verify the session status is updated in the database
        $this->entityManager->refresh($session);
        $this->assertEquals(Session::STATUS_COMPLETED, $session->getStatus());
        
        // 6. Verify redirection to review form or history page
        $this->assertResponseIsSuccessful();
    }
    
    protected function tearDown(): void
    {
        // Clean up sessions created during tests
        $testSessions = $this->sessionRepository->findBy([
            'fromUser' => $this->user1,
            'toUser' => $this->user2
        ]);
        
        foreach ($testSessions as $session) {
            $this->entityManager->remove($session);
        }
        $this->entityManager->flush();
        
        parent::tearDown();
    }
} 
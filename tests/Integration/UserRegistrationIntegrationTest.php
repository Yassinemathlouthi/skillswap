<?php

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserRegistrationIntegrationTest extends WebTestCase
{
    private $client;
    private $userRepository;
    private $passwordHasher;
    private $testUserEmail;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        
        // Create a unique test email to avoid conflicts
        $this->testUserEmail = 'test.user.' . uniqid() . '@example.com';
        
        // Clean up any existing test user (in case a previous test failed)
        $existingUser = $this->userRepository->findOneByEmail($this->testUserEmail);
        if ($existingUser) {
            $entityManager = static::getContainer()->get('doctrine')->getManager();
            $entityManager->remove($existingUser);
            $entityManager->flush();
        }
    }

    public function testCompleteRegistrationFlow(): void
    {
        // 1. Visit the registration page
        $crawler = $this->client->request('GET', '/register');
        $this->assertResponseIsSuccessful();
        
        // 2. Fill and submit the registration form
        $form = $crawler->selectButton('Register')->form([
            'registration_form[username]' => 'testuser' . uniqid(),
            'registration_form[email]' => $this->testUserEmail,
            'registration_form[plainPassword]' => 'Password123!',
            'registration_form[agreeTerms]' => true
        ]);
        
        $this->client->submit($form);
        
        // 3. Assert redirection after registration (typically to homepage or login)
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        
        // 4. Verify the user was created in the database
        $user = $this->userRepository->findOneByEmail($this->testUserEmail);
        $this->assertNotNull($user, 'User should be created in the database');
        
        // 5. Verify user data was saved correctly
        $this->assertEquals($this->testUserEmail, $user->getEmail());
        
        // 6. Verify password was hashed correctly
        $this->assertTrue(
            $this->passwordHasher->isPasswordValid($user, 'Password123!'),
            'Password should be correctly hashed'
        );
        
        // 7. Verify user has default role
        $this->assertContains('ROLE_USER', $user->getRoles());
        
        // 8. Verify registration timestamp is set
        $this->assertNotNull($user->getCreatedAt());
        $this->assertInstanceOf(\DateTimeInterface::class, $user->getCreatedAt());
        
        // 9. Test that the user can now log in
        $loginCrawler = $this->client->request('GET', '/login');
        $loginForm = $loginCrawler->selectButton('Sign in')->form([
            'email' => $this->testUserEmail,
            'password' => 'Password123!',
        ]);
        
        $this->client->submit($loginForm);
        $this->assertResponseRedirects();
        
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }
    
    public function testRegistrationWithInvalidData(): void
    {
        // 1. Visit the registration page
        $crawler = $this->client->request('GET', '/register');
        
        // 2. Submit the form with invalid data
        $form = $crawler->selectButton('Register')->form([
            'registration_form[username]' => 'a', // Too short
            'registration_form[email]' => 'not-an-email',
            'registration_form[plainPassword]' => '123', // Too simple
            'registration_form[agreeTerms]' => false // Terms not accepted
        ]);
        
        $crawler = $this->client->submit($form);
        
        // 3. Assert that we stay on the registration page with errors
        $this->assertResponseStatusCodeSame(422); // Or 200, depending on your form setup
        
        // 4. Check for validation error messages
        $this->assertSelectorTextContains('.invalid-feedback', 'email');
        $this->assertSelectorTextContains('.invalid-feedback', 'password');
        $this->assertSelectorTextContains('.invalid-feedback', 'terms');
        
        // 5. Verify no user was created in the database
        $user = $this->userRepository->findOneByEmail('not-an-email');
        $this->assertNull($user, 'No user should be created with invalid data');
    }
    
    public function testRegistrationWithExistingEmail(): void
    {
        // 1. Create a user first
        $existingUser = new User();
        $existingUser->setUsername('existinguser');
        $existingUser->setEmail('existing@example.com');
        $encodedPassword = $this->passwordHasher->hashPassword($existingUser, 'password123');
        $existingUser->setPassword($encodedPassword);
        
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($existingUser);
        $entityManager->flush();
        
        // 2. Visit the registration page
        $crawler = $this->client->request('GET', '/register');
        
        // 3. Submit the form with the existing email
        $form = $crawler->selectButton('Register')->form([
            'registration_form[username]' => 'newusername',
            'registration_form[email]' => 'existing@example.com', // Already exists
            'registration_form[plainPassword]' => 'Password123!',
            'registration_form[agreeTerms]' => true
        ]);
        
        $crawler = $this->client->submit($form);
        
        // 4. Assert that we stay on the registration page with an error
        $this->assertResponseStatusCodeSame(422); // Or 200, depending on your form setup
        
        // 5. Check for duplicate email error message
        $this->assertSelectorTextContains('.invalid-feedback', 'email already exists');
        
        // 6. Clean up
        $entityManager->remove($existingUser);
        $entityManager->flush();
    }
    
    protected function tearDown(): void
    {
        // Clean up the test user to keep the database clean
        $user = $this->userRepository->findOneByEmail($this->testUserEmail);
        if ($user) {
            $entityManager = static::getContainer()->get('doctrine')->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }
        
        parent::tearDown();
    }
} 
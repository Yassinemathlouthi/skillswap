<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use App\Repository\UserRepository;

class LoginTest extends WebTestCase
{
    public function testSuccessfulLogin(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        
        // Ensure we have a test user
        $testUser = $userRepository->findOneByEmail('test@example.com');
        
        if (!$testUser) {
            $this->markTestSkipped('Test user with email test@example.com not found. Please create a test user first.');
        }
        
        // Access the login page
        $crawler = $client->request('GET', '/login');
        
        // Check the response status code
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Login');
        
        // Submit the login form
        $form = $crawler->selectButton('Sign in')->form([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);
        
        $client->submit($form);
        
        // Assert redirection after successful login (typically to homepage)
        $this->assertResponseRedirects();
        $client->followRedirect();
        
        // Assert we are now on the dashboard or homepage
        $this->assertResponseIsSuccessful();
        
        // Verify we're logged in - check for a profile link or user menu
        $this->assertSelectorExists('a[href*="/profile"]');
    }
    
    public function testLoginWithInvalidCredentials(): void
    {
        $client = static::createClient();
        
        // Access the login page
        $crawler = $client->request('GET', '/login');
        
        // Submit the login form with invalid credentials
        $form = $crawler->selectButton('Sign in')->form([
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);
        
        $client->submit($form);
        
        // After failed login, we should be redirected back to login page
        $client->followRedirect();
        
        // Check that we have an error message
        $this->assertSelectorTextContains('.alert-danger', 'Invalid credentials');
    }
    
    public function testAccessProtectedPageWhenNotLoggedIn(): void
    {
        $client = static::createClient();
        
        // Try to access a protected page (e.g., profile page)
        $client->request('GET', '/profile');
        
        // Assert that we get redirected to login page
        $this->assertResponseRedirects('/login');
    }
    
    public function testLogout(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        
        // Ensure we have a test user
        $testUser = $userRepository->findOneByEmail('test@example.com');
        
        if (!$testUser) {
            $this->markTestSkipped('Test user with email test@example.com not found. Please create a test user first.');
        }
        
        // Login first
        $client->loginUser($testUser);
        
        // Access the homepage to verify login worked
        $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        
        // Now logout
        $client->request('GET', '/logout');
        
        // After logout, should redirect to homepage or login page
        $this->assertResponseRedirects();
        $client->followRedirect();
        
        // Try to access a protected page
        $client->request('GET', '/profile');
        
        // Should be redirected to login again, confirming logout worked
        $this->assertResponseRedirects('/login');
    }
} 
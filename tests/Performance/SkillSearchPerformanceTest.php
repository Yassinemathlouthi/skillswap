<?php

namespace App\Tests\Performance;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\SkillRepository;
use App\Repository\UserRepository;
use App\Entity\User;
use App\Entity\Skill;
use App\Entity\UserSkillOffered;
use Doctrine\ORM\EntityManagerInterface;

class SkillSearchPerformanceTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $skillRepository;
    private $userRepository;
    private $testUsers = [];
    private $testSkills = [];
    private $startTime;
    private $endTime;
    
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->skillRepository = static::getContainer()->get(SkillRepository::class);
        $this->userRepository = static::getContainer()->get(UserRepository::class);
        
        // Define benchmark thresholds
        $this->maxAllowedSearchTime = 1.0;
        $this->maxAllowedPageLoadTime = 2.0;
    }
    
    /**
     * Setup test data - create multiple skills and users with those skills
     */
    private function setupTestData(int $numUsers = 10, int $numSkills = 5): void
    {
        // Create test skills if they don't exist
        for ($i = 1; $i <= $numSkills; $i++) {
            $skillName = "Performance Test Skill $i";
            $skill = $this->skillRepository->findOneBy(['name' => $skillName]);
            
            if (!$skill) {
                $skill = new Skill();
                $skill->setName($skillName);
                $skill->setDescription("Skill for performance testing");
                $this->entityManager->persist($skill);
            }
            
            $this->testSkills[] = $skill;
        }
        
        $this->entityManager->flush();
        
        // Create test users with skills
        for ($i = 1; $i <= $numUsers; $i++) {
            $userEmail = "perf.test.user$i@example.com";
            $user = $this->userRepository->findOneByEmail($userEmail);
            
            if (!$user) {
                $user = new User();
                $user->setUsername("perf_test_user$i");
                $user->setEmail($userEmail);
                $user->setPassword('$2y$13$HOg6QQakJrfpPjSP6oGfYeRj5Nw9w5gQhEuP4Cxw4FvILQcFE09e.'); // 'password123'
                $user->setFirstName("Performance");
                $user->setLastName("Tester $i");
                $user->setLocation("Test City");
                
                // Assign random test skills to user
                foreach ($this->testSkills as $index => $skill) {
                    // Only assign some skills to each user to simulate real data distribution
                    if ($index % 2 === $i % 2) {
                        $userSkill = new UserSkillOffered();
                        $userSkill->setUser($user);
                        $userSkill->setSkill($skill);
                        $this->entityManager->persist($userSkill);
                    }
                }
                
                $this->entityManager->persist($user);
            }
            
            $this->testUsers[] = $user;
        }
        
        $this->entityManager->flush();
    }
    
    /**
     * Start timing for a benchmark
     */
    private function startBenchmark(): void
    {
        $this->startTime = microtime(true);
    }
    
    /**
     * End timing and return elapsed time in seconds
     */
    private function endBenchmark(): float
    {
        $this->endTime = microtime(true);
        return $this->endTime - $this->startTime;
    }
    
    /**
     * Test search performance with various query sizes
     */
    public function testSkillSearchPerformance(): void
    {
        // Setup test data with 20 users and 10 skills
        $this->setupTestData(20, 10);
        
        // Execute searches with different skill names
        $searchTerms = [
            'Skill 1', // Simple query
            'Performance Test', // Partial match
            'Nonexistent Skill', // No results
            'Test S', // Very partial
            'Performance Test Skill 5' // Complete name
        ];
        
        $searchResults = [];
        $searchTimes = [];
        
        foreach ($searchTerms as $term) {
            $this->startBenchmark();
            
            // Execute the search via repository
            $results = $this->skillRepository->findBySearchTerm($term);
            
            $time = $this->endBenchmark();
            $searchTimes[$term] = $time;
            $searchResults[$term] = count($results);
            
            // Assert that the search doesn't take too long
            $this->assertLessThan(
                $this->maxAllowedSearchTime,
                $time,
                "Search for '$term' took too long: $time seconds"
            );
        }
        
        // Log results
        echo "\nSkill Search Performance Results:\n";
        echo "--------------------------------\n";
        foreach ($searchTerms as $term) {
            echo sprintf(
                "Search term: '%s' - Found %d results in %.4f seconds\n",
                $term,
                $searchResults[$term],
                $searchTimes[$term]
            );
        }
        
        // Also benchmark the search via HTTP request
        $this->client->request('GET', '/skills/search?q=Performance');
        $this->assertResponseIsSuccessful();
        
        // Measure rendering time for search results page
        $this->startBenchmark();
        $this->client->request('GET', '/explore?skill=Performance Test Skill 1');
        $pageLoadTime = $this->endBenchmark();
        
        $this->assertResponseIsSuccessful();
        $this->assertLessThan(
            $this->maxAllowedPageLoadTime,
            $pageLoadTime,
            "Search results page took too long to load: $pageLoadTime seconds"
        );
        
        echo sprintf("\nSearch results page loaded in %.4f seconds\n", $pageLoadTime);
    }
    
    /**
     * Test performance of loading user profiles with many skills
     */
    public function testUserProfileLoadPerformance(): void
    {
        // Create a user with many skills (edge case)
        $powerUser = $this->userRepository->findOneByEmail('power.user@example.com');
        
        if (!$powerUser) {
            $powerUser = new User();
            $powerUser->setUsername('power_user');
            $powerUser->setEmail('power.user@example.com');
            $powerUser->setPassword('$2y$13$HOg6QQakJrfpPjSP6oGfYeRj5Nw9w5gQhEuP4Cxw4FvILQcFE09e.'); // 'password123'
            $powerUser->setFirstName('Power');
            $powerUser->setLastName('User');
            
            // Create many skills for this user
            for ($i = 1; $i <= 20; $i++) {
                $skillName = "Power User Skill $i";
                $skill = $this->skillRepository->findOneBy(['name' => $skillName]);
                
                if (!$skill) {
                    $skill = new Skill();
                    $skill->setName($skillName);
                    $skill->setDescription("Skill $i for power user");
                    $this->entityManager->persist($skill);
                    $this->entityManager->flush();
                }
                
                $userSkill = new UserSkillOffered();
                $userSkill->setUser($powerUser);
                $userSkill->setSkill($skill);
                $this->entityManager->persist($userSkill);
            }
            
            $this->entityManager->persist($powerUser);
            $this->entityManager->flush();
        }
        
        // Measure profile page load time
        $this->startBenchmark();
        $this->client->request('GET', '/profile/' . $powerUser->getId());
        $profileLoadTime = $this->endBenchmark();
        
        $this->assertResponseIsSuccessful();
        $this->assertLessThan(
            $this->maxAllowedPageLoadTime,
            $profileLoadTime,
            "Profile page with many skills took too long to load: $profileLoadTime seconds"
        );
        
        echo sprintf("\nProfile page with many skills loaded in %.4f seconds\n", $profileLoadTime);
    }
    
    protected function tearDown(): void
    {
        // Clean up is optional for performance tests
        // In some cases, keeping the test data might be useful for repeated benchmarking
        
        parent::tearDown();
    }
} 
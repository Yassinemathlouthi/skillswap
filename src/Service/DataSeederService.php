<?php

namespace App\Service;

use App\Document\User;
use App\Document\SkillCategory;
use App\Document\Session;
use App\Document\Message;
use App\Document\Review;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class DataSeederService
{
    private MongoDBService $mongodb;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        MongoDBService $mongodb,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->mongodb = $mongodb;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Seed all data
     */
    public function seedAll(): void
    {
        $this->seedUsers();
        $this->seedSkillCategories();
        $this->seedSessions();
        $this->seedMessages();
        $this->seedReviews();
    }

    /**
     * Seed sample users
     */
    public function seedUsers(): void
    {
        // Check if users already exist
        if (!empty($this->mongodb->getCollection('users'))) {
            return;
        }

        // Create admin user
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setUsername('admin');
        $admin->setFirstName('Admin');
        $admin->setLastName('User');
        $admin->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $admin->setSkillsOffered(['Web Development', 'Project Management']);
        $admin->setSkillsWanted(['German Language', 'Piano']);
        $admin->setBio('Administrator of SkillSwap platform.');
        $admin->setLocation('New York');
        $admin->setRegisteredAt(new \DateTimeImmutable('2023-01-01'));
        
        // Hash the password
        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'admin123');
        $admin->setPassword($hashedPassword);
        
        // Save to database using toArray() method
        $this->mongodb->insertDocument('users', $admin->toArray());

        // Create regular users
        $users = [
            [
                'email' => 'john@example.com',
                'username' => 'john',
                'firstName' => 'John',
                'lastName' => 'Doe',
                'password' => 'password123',
                'roles' => ['ROLE_USER'],
                'skillsOffered' => ['Spanish Language', 'Guitar', 'Cooking'],
                'skillsWanted' => ['Programming', 'Photography'],
                'bio' => 'Spanish teacher and musician who loves cooking. Looking to learn programming and photography.',
                'location' => 'Los Angeles',
            ],
            [
                'email' => 'jane@example.com',
                'username' => 'jane',
                'firstName' => 'Jane',
                'lastName' => 'Smith',
                'password' => 'password123',
                'roles' => ['ROLE_USER'],
                'skillsOffered' => ['Photography', 'Yoga', 'French Language'],
                'skillsWanted' => ['Guitar', 'Digital Marketing'],
                'bio' => 'Professional photographer with passion for yoga and languages.',
                'location' => 'San Francisco',
            ],
            [
                'email' => 'michael@example.com',
                'username' => 'michael',
                'firstName' => 'Michael',
                'lastName' => 'Johnson',
                'password' => 'password123',
                'roles' => ['ROLE_USER'],
                'skillsOffered' => ['Programming', 'Digital Marketing', 'SEO'],
                'skillsWanted' => ['Spanish Language', 'Cooking'],
                'bio' => 'Software developer with marketing experience, wanting to learn new languages and cooking.',
                'location' => 'Chicago',
            ],
            [
                'email' => 'emma@example.com',
                'username' => 'emma',
                'firstName' => 'Emma',
                'lastName' => 'Williams',
                'password' => 'password123',
                'roles' => ['ROLE_USER'],
                'skillsOffered' => ['Piano', 'German Language', 'Mathematics'],
                'skillsWanted' => ['Web Development', 'Yoga'],
                'bio' => 'Music teacher and language enthusiast.',
                'location' => 'Boston',
            ],
        ];

        foreach ($users as $userData) {
            $user = new User();
            $user->setEmail($userData['email']);
            $user->setUsername($userData['username']);
            $user->setFirstName($userData['firstName']);
            $user->setLastName($userData['lastName']);
            $user->setRoles($userData['roles']);
            $user->setSkillsOffered($userData['skillsOffered']);
            $user->setSkillsWanted($userData['skillsWanted']);
            $user->setBio($userData['bio']);
            $user->setLocation($userData['location']);
            $user->setRegisteredAt(new \DateTimeImmutable('2023-01-01'));
            
            // Hash the password
            $hashedPassword = $this->passwordHasher->hashPassword($user, $userData['password']);
            $user->setPassword($hashedPassword);
            
            // Save to database
            $this->mongodb->insertDocument('users', $user->toArray());
        }
    }

    /**
     * Seed skill categories
     */
    public function seedSkillCategories(): void
    {
        // Check if categories already exist
        if (!empty($this->mongodb->getCollection('skill_categories'))) {
            return;
        }

        $categories = [
            [
                'name' => 'Programming',
                'description' => 'Software development and coding skills',
                'icon' => 'fa-code',
                'skills' => ['Web Development', 'Mobile App Development', 'Python', 'JavaScript', 'Java', 'C#', 'Ruby', 'PHP', 'Data Analysis', 'Machine Learning'],
            ],
            [
                'name' => 'Languages',
                'description' => 'Foreign language learning and practice',
                'icon' => 'fa-language',
                'skills' => ['English', 'Spanish', 'French', 'German', 'Chinese', 'Japanese', 'Russian', 'Italian', 'Portuguese', 'Arabic'],
            ],
            [
                'name' => 'Music',
                'description' => 'Musical instruments and theory',
                'icon' => 'fa-music',
                'skills' => ['Piano', 'Guitar', 'Violin', 'Drums', 'Singing', 'Music Production', 'Music Theory', 'DJ Skills', 'Saxophone', 'Flute'],
            ],
            [
                'name' => 'Arts & Crafts',
                'description' => 'Creative arts and handmade crafts',
                'icon' => 'fa-palette',
                'skills' => ['Painting', 'Drawing', 'Sculpture', 'Photography', 'Knitting', 'Sewing', 'Pottery', 'Jewelry Making', 'Woodworking', 'Origami'],
            ],
            [
                'name' => 'Fitness',
                'description' => 'Physical fitness and wellbeing',
                'icon' => 'fa-dumbbell',
                'skills' => ['Yoga', 'Pilates', 'Running', 'Weight Training', 'Swimming', 'Cycling', 'Martial Arts', 'Dance', 'HIIT', 'Meditation'],
            ],
            [
                'name' => 'Cooking',
                'description' => 'Culinary skills and food preparation',
                'icon' => 'fa-utensils',
                'skills' => ['Baking', 'Italian Cuisine', 'Asian Cuisine', 'Vegetarian Cooking', 'Desserts', 'BBQ', 'Sushi Making', 'Meal Prep', 'Cocktail Mixing', 'Wine Tasting'],
            ],
            [
                'name' => 'Business',
                'description' => 'Business and entrepreneurial skills',
                'icon' => 'fa-briefcase',
                'skills' => ['Marketing', 'Accounting', 'Sales', 'Project Management', 'Public Speaking', 'Negotiation', 'Leadership', 'Digital Marketing', 'SEO', 'Social Media Management'],
            ],
            [
                'name' => 'Academic',
                'description' => 'Educational and academic subjects',
                'icon' => 'fa-graduation-cap',
                'skills' => ['Mathematics', 'Physics', 'Chemistry', 'Biology', 'History', 'Literature', 'Philosophy', 'Economics', 'Statistics', 'Psychology'],
            ],
        ];

        foreach ($categories as $categoryData) {
            $category = new SkillCategory();
            $category->setName($categoryData['name']);
            $category->setDescription($categoryData['description']);
            $category->setIcon($categoryData['icon']);
            $category->setSkills($categoryData['skills']);
            $category->setUpdatedAt(new \DateTimeImmutable());
            
            // Save to database
            $this->mongodb->insertDocument('skill_categories', $category->toArray());
        }
    }

    /**
     * Seed session data
     */
    public function seedSessions(): void
    {
        // Check if sessions already exist
        if (!empty($this->mongodb->getCollection('sessions'))) {
            return;
        }

        // Get user IDs
        $users = $this->mongodb->getCollection('users');
        if (count($users) < 2) {
            return;
        }

        // Create sessions between users
        $statuses = ['pending', 'confirmed', 'completed', 'canceled'];
        $skills = ['Programming', 'Spanish Language', 'Guitar', 'Photography', 'Yoga'];
        
        // Create different sessions with different statuses
        for ($i = 0; $i < count($users) - 1; $i++) {
            for ($j = $i + 1; $j < count($users); $j++) {
                // Create two sessions between each pair of users
                for ($k = 0; $k < 2; $k++) {
                    $fromUser = $k === 0 ? $users[$i] : $users[$j];
                    $toUser = $k === 0 ? $users[$j] : $users[$i];
                    $status = $statuses[array_rand($statuses)];
                    $skill = $skills[array_rand($skills)];
                    
                    // Set date based on status
                    $date = new \DateTimeImmutable();
                    if ($status === 'completed') {
                        $date = $date->modify('-' . rand(1, 30) . ' days');
                    } else if ($status === 'confirmed' || $status === 'pending') {
                        $date = $date->modify('+' . rand(1, 30) . ' days');
                    }
                    
                    $session = new Session();
                    $session->setFromUserId($fromUser['_id']);
                    $session->setToUserId($toUser['_id']);
                    $session->setStatus($status);
                    $session->setDateTime($date);
                    $session->setSkill($skill);
                    $session->setNotes('I would like to learn about ' . $skill . ' from you.');
                    $session->setUpdatedAt(new \DateTimeImmutable('-' . rand(1, 60) . ' days'));
                    
                    // Save to database
                    $this->mongodb->insertDocument('sessions', $session->toArray());
                }
            }
        }
    }

    /**
     * Seed message data
     */
    public function seedMessages(): void
    {
        // Check if messages already exist
        if (!empty($this->mongodb->getCollection('messages'))) {
            return;
        }

        // Get user IDs
        $users = $this->mongodb->getCollection('users');
        if (count($users) < 2) {
            return;
        }

        // Sample message contents
        $messageContents = [
            'Hi there! I saw you\'re offering to teach {skill}. I\'d love to learn from you!',
            'I noticed you want to learn {skill}. I can help you with that!',
            'When are you available for a session on {skill}?',
            'Thanks for accepting my session request. Looking forward to it!',
            'I really enjoyed our last session. Would you be up for another one?',
            'Do you have any recommended resources for learning {skill}?',
            'Just checking in to confirm our session for tomorrow.',
            'I might be 5 minutes late to our session. Is that okay?',
            'Thanks for the great session yesterday!',
        ];

        // Create messages between users
        for ($i = 0; $i < count($users) - 1; $i++) {
            for ($j = $i + 1; $j < count($users); $j++) {
                // Create a conversation between each pair of users
                $numMessages = rand(3, 10);
                $senderIndex = rand(0, 1) ? $i : $j;
                $receiverIndex = $senderIndex === $i ? $j : $i;
                
                for ($k = 0; $k < $numMessages; $k++) {
                    // Alternate sender and receiver
                    if ($k % 2 === 0) {
                        $senderId = $users[$senderIndex]['_id'];
                        $receiverId = $users[$receiverIndex]['_id'];
                    } else {
                        $senderId = $users[$receiverIndex]['_id'];
                        $receiverId = $users[$senderIndex]['_id'];
                    }
                    
                    // Random skill
                    $skills = array_merge(
                        $users[$senderIndex]['skillsOffered'] ?? [],
                        $users[$receiverIndex]['skillsWanted'] ?? []
                    );
                    $skill = !empty($skills) ? $skills[array_rand($skills)] : 'this skill';
                    
                    // Create message
                    $content = str_replace('{skill}', $skill, $messageContents[array_rand($messageContents)]);
                    
                    $message = new Message();
                    $message->setSenderId($senderId);
                    $message->setReceiverId($receiverId);
                    $message->setContent($content);
                    $message->setTimestamp(new \DateTimeImmutable('-' . rand(1, 30) . ' days'));
                    $message->setIsRead(rand(0, 1) === 1);
                    
                    // Save to database
                    $this->mongodb->insertDocument('messages', $message->toArray());
                }
            }
        }
    }

    /**
     * Seed review data
     */
    public function seedReviews(): void
    {
        // Check if reviews already exist
        if (!empty($this->mongodb->getCollection('reviews'))) {
            return;
        }

        // Get completed sessions
        $sessions = $this->mongodb->findBy('sessions', ['status' => 'completed']);
        if (empty($sessions)) {
            return;
        }

        // Sample comments for different ratings
        $reviewComments = [
            1 => [
                'Not a helpful session at all. The teacher was unprepared.',
                'Very disappointing experience. Would not recommend.',
            ],
            2 => [
                'Below average experience. There were a lot of issues.',
                'Not what I expected. Some good points but overall disappointing.',
            ],
            3 => [
                'Average session. Nothing special but not bad either.',
                'Okay experience. Some helpful tips but could be improved.',
            ],
            4 => [
                'Great session! I learned a lot and the teacher was well-prepared.',
                'Very helpful and knowledgeable. Would recommend.',
            ],
            5 => [
                'Excellent session! The teacher was extremely knowledgeable and patient.',
                'Amazing experience. I learned so much in just one session!',
            ]
        ];

        // Create a review for each completed session
        foreach ($sessions as $session) {
            // Skip sessions that don't have both fromUserId and toUserId
            if (!isset($session['fromUserId']) || !isset($session['toUserId'])) {
                continue;
            }
            
            // Randomly decide if the review is from the session requester or the other user
            $isFromRequester = rand(0, 1) === 1;
            
            $reviewerId = $isFromRequester ? $session['fromUserId'] : $session['toUserId'];
            $reviewedUserId = $isFromRequester ? $session['toUserId'] : $session['fromUserId'];
            
            // Random rating between 3-5 (mostly positive)
            $rating = rand(3, 5);
            
            // Get a random comment for this rating
            $comments = $reviewComments[$rating];
            $comment = $comments[array_rand($comments)];
            
            $review = new Review();
            $review->setReviewerId($reviewerId);
            $review->setReviewedUserId($reviewedUserId);
            $review->setSessionId($session['_id']);
            $review->setRating($rating);
            $review->setComment($comment);
            $review->setCreatedAt(new \DateTimeImmutable('-' . rand(1, 30) . ' days'));
            
            // Save to database
            $this->mongodb->insertDocument('reviews', $review->toArray());
        }
    }
} 
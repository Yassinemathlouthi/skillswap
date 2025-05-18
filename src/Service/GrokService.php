<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Response;

class GrokService
{
    private HttpClientInterface $httpClient;
    private string $apiKey;
    private string $apiEndpoint = 'https://api.groq.com/openai/v1/chat/completions';
    private array $fallbackResponses = [
        'help' => 'To get help with SkillSwap, check out our FAQ section or contact support via email at support@skillswap.com.',
        'skill' => 'SkillSwap lets you both offer skills you can teach and request to learn skills from others. You can browse available skills by category or search for specific topics.',
        'profile' => 'To set up your profile, go to the Profile page, add a photo, write a bio, and list your skills/interests. A complete profile increases your chances of finding skill-sharing partners!',
        'session' => 'Sessions are how skills are exchanged on our platform. You can request a session with a teacher, schedule a time, meet virtually or in-person, and then leave reviews afterward.',
        'payment' => 'SkillSwap is primarily based on exchanging skills rather than money. However, some premium users may charge for specialized sessions.',
        'review' => 'After completing a session, you can leave a review rating the experience from 1-5 stars and adding comments. Reviews help build trust in our community.',
        'message' => 'You can message other users through our secure messaging system. Click on a user\'s profile and select "Send Message" to start a conversation.',
        'account' => 'To manage your account settings, click on your profile picture in the top right corner and select "Account Settings".',
        'default' => 'Welcome to SkillSwap! I\'m your assistant here to help with any questions about our skill-sharing platform. You can ask about creating your profile, finding skills, scheduling sessions, and more!'
    ];

    public function __construct(
        HttpClientInterface $httpClient,
        ParameterBagInterface $params
    ) {
        $this->httpClient = $httpClient;
        $this->apiKey = $params->get('grok_api_key');
    }

    public function getResponse(string $message): array
    {
        // Check if API key is set or a test key
        if (empty($this->apiKey) || $this->apiKey === '%env(GROK_API_KEY)%' || strpos($this->apiKey, 'gsk_') !== 0) {
            return [
                'success' => true,
                'message' => $this->getFallbackResponse($message),
            ];
        }
        
        try {
            // Build the request payload for Groq API (which supports OpenAI format)
            $payload = [
                'model' => 'llama3-70b-8192',  // Using LLaMa3 model via Groq
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a helpful SkillSwap assistant. Provide concise, friendly answers about the skill-sharing platform.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $message
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 800
            ];

            // Make the API request
            $response = $this->httpClient->request('POST', $this->apiEndpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            // Process the response
            $data = $response->toArray();
            
            return [
                'success' => true,
                'message' => $data['choices'][0]['message']['content'] ?? 'Sorry, I couldn\'t generate a response.',
            ];
            
        } catch (\Exception $e) {
            // For any exception, return a fallback response
            return [
                'success' => false,
                'message' => 'An error occurred while communicating with the AI service: ' . $e->getMessage(),
                'fallback' => $this->getFallbackResponse($message)
            ];
        }
    }
    
    /**
     * Get a simple fallback response based on keywords in the user's message
     */
    private function getFallbackResponse(string $message): string
    {
        $message = strtolower($message);
        
        foreach ($this->fallbackResponses as $keyword => $response) {
            if ($keyword !== 'default' && strpos($message, $keyword) !== false) {
                return $response;
            }
        }
        
        return $this->fallbackResponses['default'];
    }
} 
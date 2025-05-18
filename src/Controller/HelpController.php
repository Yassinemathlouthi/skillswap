<?php

namespace App\Controller;

use App\Entity\Faq;
use App\Repository\FaqRepository;
use App\Service\GrokService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/help')]
class HelpController extends AbstractController
{
    #[Route('', name: 'app_help')]
    public function index(): Response
    {
        // Main help center landing page
        return $this->render('help/index.html.twig', [
            'current_page' => 'overview'
        ]);
    }

    #[Route('/faq', name: 'app_help_faq')]
    public function faq(FaqRepository $faqRepository, Request $request): Response
    {
        try {
            // Search functionality
            $searchTerm = $request->query->get('q');
            
            if ($searchTerm) {
                $faqs = $faqRepository->findBySearchTerm($searchTerm);
                $categories = [];
                
                // Extract unique categories from the search results
                foreach ($faqs as $faq) {
                    if (!in_array($faq->getCategory(), $categories)) {
                        $categories[] = $faq->getCategory();
                    }
                }
            } else {
                // Get all categories and FAQs
                try {
                    $categories = $faqRepository->findAllCategories();
                } catch (\Exception $e) {
                    // If categories can't be retrieved, use an empty array
                    $categories = [];
                }
                
                try {
                    $faqs = $faqRepository->findAllPublished();
                } catch (\Exception $e) {
                    // If FAQs can't be retrieved, use an empty array
                    $faqs = [];
                }
            }
            
            // Group FAQs by category
            $faqsByCategory = [];
            foreach ($categories as $category) {
                $faqsByCategory[$category] = array_filter($faqs, function($faq) use ($category) {
                    return $faq->getCategory() === $category;
                });
            }
            
            return $this->render('help/faq.html.twig', [
                'faqsByCategory' => $faqsByCategory,
                'searchTerm' => $searchTerm,
                'current_page' => 'faq'
            ]);
        } catch (\Exception $e) {
            // In case of any database or other errors, show empty FAQ page with an error message
            return $this->render('help/faq.html.twig', [
                'faqsByCategory' => [],
                'searchTerm' => $request->query->get('q'),
                'current_page' => 'faq',
                'error' => 'We\'re experiencing technical difficulties with our FAQ database. Please try again later or contact support.'
            ]);
        }
    }

    #[Route('/getting-started', name: 'app_help_getting_started')]
    public function gettingStarted(): Response
    {
        return $this->render('help/getting_started.html.twig', [
            'current_page' => 'getting_started'
        ]);
    }

    #[Route('/best-practices', name: 'app_help_best_practices')]
    public function bestPractices(): Response
    {
        return $this->render('help/best_practices.html.twig', [
            'current_page' => 'best_practices'
        ]);
    }

    #[Route('/contact', name: 'app_help_contact')]
    public function contact(): Response
    {
        return $this->render('help/contact.html.twig', [
            'current_page' => 'contact'
        ]);
    }
    
    #[Route('/ai-chat', name: 'app_help_ai_chat')]
    public function aiChat(): Response
    {
        return $this->render('help/ai_chat.html.twig', [
            'current_page' => 'ai_chat'
        ]);
    }
    
    #[Route('/api/chat', name: 'app_help_api_chat', methods: ['POST'])]
    public function apiChat(Request $request, GrokService $grokService): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $message = $data['message'] ?? '';
            
            if (empty($message)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Message cannot be empty'
                ]);
            }
            
            // Get response from Grok
            $response = $grokService->getResponse($message);
            
            if (!$response['success']) {
                // If the service returned an error, log it in dev environment
                if ($this->getParameter('kernel.environment') === 'dev') {
                    error_log('AI Service Error: ' . $response['message']);
                }
                
                // Use fallback response if available, otherwise use generic message
                if (isset($response['fallback'])) {
                    $response['message'] = $response['fallback'];
                    $response['success'] = true;
                } else {
                    // Provide a more user-friendly message
                    $response['message'] = 'I apologize, but I\'m having trouble connecting to the AI service. Please try again later or contact our support team for assistance.';
                }
            }
            
            return $this->json($response);
            
        } catch (\Exception $e) {
            // Log the exception in dev environment
            if ($this->getParameter('kernel.environment') === 'dev') {
                error_log('Exception in AI Chat: ' . $e->getMessage());
            }
            
            return $this->json([
                'success' => false,
                'message' => 'Sorry, something went wrong with the AI assistant. Please try again later.'
            ]);
        }
    }
} 
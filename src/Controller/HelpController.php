<?php

namespace App\Controller;

use App\Entity\Faq;
use App\Repository\FaqRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
            $categories = $faqRepository->findAllCategories();
            $faqs = $faqRepository->findAllPublished();
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
} 
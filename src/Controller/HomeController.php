<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\SkillRepository;
use App\Repository\SkillCategoryRepository;
use App\Repository\UserSkillOfferedRepository;
use App\Repository\UserSkillWantedRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(UserRepository $userRepository, SkillCategoryRepository $categoryRepository): Response
    {
        // Get featured users (in a real app, this would be users with high ratings or many skills)
        $featuredUsers = $userRepository->findAll();
        // Limit to 6 users for the home page
        $featuredUsers = array_slice($featuredUsers, 0, 6);
        
        // Get skill categories
        $categories = $categoryRepository->findAll();
        
        return $this->render('home/index.html.twig', [
            'featured_users' => $featuredUsers,
            'categories' => $categories,
        ]);
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function dashboard(): Response
    {
        // Ensure user is logged in
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        return $this->render('home/dashboard.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/skills', name: 'app_skills')]
    public function skills(
        SkillCategoryRepository $categoryRepository, 
        SkillRepository $skillRepository,
        UserRepository $userRepository,
        Request $request
    ): Response {
        // Get all categories
        $categories = $categoryRepository->findAll();
        
        // Get search parameters
        $selectedCategoryId = $request->query->get('category');
        $searchTerm = $request->query->get('q');
        
        // Initialize variables
        $selectedCategory = null;
        $skillsInCategory = [];
        $matchingUsers = [];
        
        // If a category is specified, find it and its skills
        if ($selectedCategoryId) {
            $selectedCategory = $categoryRepository->find($selectedCategoryId);
            
            if ($selectedCategory) {
                // Get skills in this category
                $skillsInCategory = $skillRepository->findByCategory($selectedCategory);
            }
        }
        
        // If search term is provided, search for skills and users
        if ($searchTerm) {
            // Find skills matching the search term
            $matchingSkills = $skillRepository->findBySearchTerm($searchTerm);
            
            // Find users who offer or want these skills
            $matchingUsers = $userRepository->findBySkills($matchingSkills);
        } elseif ($selectedCategory) {
            // If no search term but category is selected, find users with skills in this category
            $matchingUsers = $userRepository->findBySkillsInCategory($selectedCategory);
        }
        
        return $this->render('home/skills.html.twig', [
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'skills' => $skillsInCategory,
            'users' => $matchingUsers,
            'searchTerm' => $searchTerm,
        ]);
    }

    #[Route('/api/skills/search', name: 'app_api_skills_search')]
    public function searchSkills(
        Request $request, 
        SkillRepository $skillRepository,
        UserRepository $userRepository
    ): JsonResponse {
        // Get search term and category
        $searchTerm = $request->query->get('q');
        $categoryId = $request->query->get('category');
        
        if (!$searchTerm) {
            return new JsonResponse([
                'skills' => [],
                'users' => []
            ]);
        }
        
        // Search for skills
        $matchingSkills = $skillRepository->findBySearchTerm($searchTerm, $categoryId);
        
        // Search for users with those skills
        $matchingUsers = $userRepository->findBySkills($matchingSkills);
        
        // Format results
        $formattedSkills = [];
        foreach ($matchingSkills as $skill) {
            $formattedSkills[] = [
                'id' => $skill->getId(),
                'name' => $skill->getName(),
                'categories' => array_map(function($cs) {
                    return [
                        'id' => $cs->getCategory()->getId(),
                        'name' => $cs->getCategory()->getName()
                    ];
                }, iterator_to_array($skill->getSkillCategories()))
            ];
        }
        
        $formattedUsers = [];
        foreach ($matchingUsers as $user) {
            $formattedUsers[] = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'avatar' => $user->getAvatar(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'location' => $user->getLocation(),
                'url' => $this->generateUrl('app_user_profile', ['username' => $user->getUsername()])
            ];
        }
        
        return new JsonResponse([
            'skills' => $formattedSkills,
            'users' => $formattedUsers
        ]);
    }
} 
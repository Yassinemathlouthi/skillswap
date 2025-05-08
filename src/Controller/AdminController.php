<?php

namespace App\Controller;

use App\Document\SkillCategory;
use App\Repository\ReviewRepository;
use App\Repository\SessionRepository;
use App\Repository\SkillCategoryRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin')]
    public function index(UserRepository $userRepository, SessionRepository $sessionRepository, ReviewRepository $reviewRepository): Response
    {
        // Ensure user is an admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        // Get counts for the dashboard
        $userCount = count($userRepository->findAll());
        $sessionCount = count($sessionRepository->findAll());
        $reviewCount = count($reviewRepository->findAll());
        
        // Get recent users
        $recentUsers = $userRepository->findAll();
        // In a real app with pagination, we'd limit this properly
        $recentUsers = array_slice($recentUsers, 0, 5);
        
        // Get recently reported items (placeholder)
        $reportedItems = [];
        
        return $this->render('admin/index.html.twig', [
            'user_count' => $userCount,
            'session_count' => $sessionCount,
            'review_count' => $reviewCount,
            'recent_users' => $recentUsers,
            'reported_items' => $reportedItems,
        ]);
    }
    
    #[Route('/users', name: 'app_admin_users')]
    public function users(UserRepository $userRepository): Response
    {
        // Ensure user is an admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $users = $userRepository->findAll();
        
        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }
    
    #[Route('/users/{id}/toggle-role', name: 'app_admin_toggle_role', methods: ['POST'])]
    public function toggleUserRole(string $id, UserRepository $userRepository, Request $request): Response
    {
        // Ensure user is an admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $user = $userRepository->find($id);
        
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }
        
        // CSRF protection
        if (!$this->isCsrfTokenValid('toggle-role'.$user->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }
        
        // Toggle admin role
        $roles = $user->getRoles();
        if (in_array('ROLE_ADMIN', $roles)) {
            // Remove admin role
            $roles = array_diff($roles, ['ROLE_ADMIN']);
        } else {
            // Add admin role
            $roles[] = 'ROLE_ADMIN';
        }
        
        $user->setRoles($roles);
        $userRepository->save($user);
        
        $this->addFlash('success', 'User roles updated successfully.');
        
        return $this->redirectToRoute('app_admin_users');
    }
    
    #[Route('/users/{id}/delete', name: 'app_admin_delete_user', methods: ['POST'])]
    public function deleteUser(string $id, UserRepository $userRepository, Request $request): Response
    {
        // Ensure user is an admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $user = $userRepository->find($id);
        
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }
        
        // CSRF protection
        if (!$this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }
        
        // In a complete application, we would also delete associated data like sessions, messages, etc.
        $userRepository->remove($user);
        
        $this->addFlash('success', 'User deleted successfully.');
        
        return $this->redirectToRoute('app_admin_users');
    }
    
    #[Route('/categories', name: 'app_admin_categories')]
    public function categories(SkillCategoryRepository $categoryRepository): Response
    {
        // Ensure user is an admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $categories = $categoryRepository->findAll();
        
        return $this->render('admin/categories.html.twig', [
            'categories' => $categories,
        ]);
    }
    
    #[Route('/categories/new', name: 'app_admin_category_new', methods: ['GET', 'POST'])]
    public function newCategory(Request $request, SkillCategoryRepository $categoryRepository): Response
    {
        // Ensure user is an admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $description = $request->request->get('description');
            $icon = $request->request->get('icon');
            $skills = $request->request->get('skills');
            
            // Simple validation
            if (!$name) {
                $this->addFlash('error', 'Name is required.');
                return $this->redirectToRoute('app_admin_category_new');
            }
            
            // Check if category with this name already exists
            $existingCategory = $categoryRepository->findByName($name);
            if ($existingCategory) {
                $this->addFlash('error', 'A category with this name already exists.');
                return $this->redirectToRoute('app_admin_category_new');
            }
            
            // Process skills (comma-separated list)
            $skillsArray = [];
            if ($skills) {
                $skillsArray = array_map('trim', explode(',', $skills));
            }
            
            // Create and save the category
            $category = new SkillCategory();
            $category->setName($name);
            $category->setDescription($description);
            $category->setIcon($icon);
            $category->setSkills($skillsArray);
            
            $categoryRepository->save($category);
            
            $this->addFlash('success', 'Category created successfully.');
            return $this->redirectToRoute('app_admin_categories');
        }
        
        return $this->render('admin/category_new.html.twig');
    }
    
    #[Route('/categories/{id}/edit', name: 'app_admin_category_edit', methods: ['GET', 'POST'])]
    public function editCategory(string $id, Request $request, SkillCategoryRepository $categoryRepository): Response
    {
        // Ensure user is an admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $category = $categoryRepository->find($id);
        
        if (!$category) {
            throw $this->createNotFoundException('Category not found');
        }
        
        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $description = $request->request->get('description');
            $icon = $request->request->get('icon');
            $skills = $request->request->get('skills');
            
            // Simple validation
            if (!$name) {
                $this->addFlash('error', 'Name is required.');
                return $this->redirectToRoute('app_admin_category_edit', ['id' => $id]);
            }
            
            // Check if another category with this name already exists
            $existingCategory = $categoryRepository->findByName($name);
            if ($existingCategory && $existingCategory->getId() !== $category->getId()) {
                $this->addFlash('error', 'Another category with this name already exists.');
                return $this->redirectToRoute('app_admin_category_edit', ['id' => $id]);
            }
            
            // Process skills (comma-separated list)
            $skillsArray = [];
            if ($skills) {
                $skillsArray = array_map('trim', explode(',', $skills));
            }
            
            // Update the category
            $category->setName($name);
            $category->setDescription($description);
            $category->setIcon($icon);
            $category->setSkills($skillsArray);
            
            $categoryRepository->save($category);
            
            $this->addFlash('success', 'Category updated successfully.');
            return $this->redirectToRoute('app_admin_categories');
        }
        
        // For GET requests, just render the form with the category data
        return $this->render('admin/category_edit.html.twig', [
            'category' => $category,
            // Convert skills array to comma-separated string for the form
            'skills_string' => implode(', ', $category->getSkills()),
        ]);
    }
    
    #[Route('/categories/{id}/delete', name: 'app_admin_category_delete', methods: ['POST'])]
    public function deleteCategory(string $id, Request $request, SkillCategoryRepository $categoryRepository): Response
    {
        // Ensure user is an admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $category = $categoryRepository->find($id);
        
        if (!$category) {
            throw $this->createNotFoundException('Category not found');
        }
        
        // CSRF protection
        if (!$this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }
        
        // Delete the category
        $categoryRepository->remove($category);
        
        $this->addFlash('success', 'Category deleted successfully.');
        
        return $this->redirectToRoute('app_admin_categories');
    }
    
    #[Route('/reviews', name: 'app_admin_reviews')]
    public function reviews(ReviewRepository $reviewRepository, UserRepository $userRepository): Response
    {
        // Ensure user is an admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $reviews = $reviewRepository->findAll();
        
        // Get user objects for each review to display names
        $reviewData = [];
        foreach ($reviews as $review) {
            $reviewer = $userRepository->find($review->getReviewerId());
            $reviewedUser = $userRepository->find($review->getReviewedUserId());
            
            $reviewData[] = [
                'review' => $review,
                'reviewer' => $reviewer,
                'reviewed_user' => $reviewedUser,
            ];
        }
        
        return $this->render('admin/reviews.html.twig', [
            'review_data' => $reviewData,
        ]);
    }
    
    #[Route('/reviews/{id}/delete', name: 'app_admin_review_delete', methods: ['POST'])]
    public function deleteReview(string $id, Request $request, ReviewRepository $reviewRepository): Response
    {
        // Ensure user is an admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $review = $reviewRepository->find($id);
        
        if (!$review) {
            throw $this->createNotFoundException('Review not found');
        }
        
        // CSRF protection
        if (!$this->isCsrfTokenValid('delete'.$review->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }
        
        // Delete the review
        $reviewRepository->remove($review);
        
        $this->addFlash('success', 'Review deleted successfully.');
        
        return $this->redirectToRoute('app_admin_reviews');
    }
} 
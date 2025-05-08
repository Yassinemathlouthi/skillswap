<?php

namespace App\Controller;

use App\Document\Review;
use App\Repository\ReviewRepository;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/reviews')]
class ReviewController extends AbstractController
{
    #[Route('/', name: 'app_reviews')]
    public function index(ReviewRepository $reviewRepository): Response
    {
        // Ensure user is logged in
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $currentUser = $this->getUser();
        
        // Get reviews written by the current user
        $writtenReviews = $reviewRepository->findByReviewer($currentUser->getId());
        
        // Get reviews about the current user
        $receivedReviews = $reviewRepository->findByReviewedUser($currentUser->getId());
        
        return $this->render('review/index.html.twig', [
            'written_reviews' => $writtenReviews,
            'received_reviews' => $receivedReviews,
        ]);
    }
    
    #[Route('/new/session/{sessionId}', name: 'app_review_new_from_session')]
    public function newFromSession(string $sessionId, Request $request, SessionRepository $sessionRepository, ReviewRepository $reviewRepository, UserRepository $userRepository): Response
    {
        // Ensure user is logged in
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $currentUser = $this->getUser();
        $session = $sessionRepository->find($sessionId);
        
        if (!$session) {
            throw $this->createNotFoundException('Session not found');
        }
        
        // Check if the session is completed
        if ($session->getStatus() !== 'completed') {
            $this->addFlash('error', 'You can only review completed sessions.');
            return $this->redirectToRoute('app_sessions');
        }
        
        // Check if the current user is part of this session
        if ($session->getFromUserId() !== $currentUser->getId() && $session->getToUserId() !== $currentUser->getId()) {
            throw $this->createAccessDeniedException('You do not have access to this session');
        }
        
        // Determine who the current user is reviewing
        $reviewedUserId = ($session->getFromUserId() === $currentUser->getId()) 
            ? $session->getToUserId() 
            : $session->getFromUserId();
        
        $reviewedUser = $userRepository->find($reviewedUserId);
        
        if (!$reviewedUser) {
            throw $this->createNotFoundException('User not found');
        }
        
        // Check if the user has already submitted a review for this session
        $existingReview = $reviewRepository->findBySession($sessionId);
        if ($existingReview && $existingReview->getReviewerId() === $currentUser->getId()) {
            $this->addFlash('info', 'You have already reviewed this session.');
            return $this->redirectToRoute('app_reviews');
        }
        
        // Here we would normally process a form submission
        // For now, we'll create a review with default values
        
        if ($request->isMethod('POST')) {
            $rating = (int) $request->request->get('rating');
            $comment = $request->request->get('comment');
            
            // Validate input
            if ($rating < 1 || $rating > 5) {
                $this->addFlash('error', 'Rating must be between 1 and 5.');
                return $this->redirectToRoute('app_review_new_from_session', ['sessionId' => $sessionId]);
            }
            
            // Create and save the review
            $review = new Review();
            $review->setReviewerId($currentUser->getId());
            $review->setReviewedUserId($reviewedUserId);
            $review->setSessionId($sessionId);
            $review->setRating($rating);
            $review->setComment($comment);
            
            $reviewRepository->save($review);
            
            $this->addFlash('success', 'Your review has been submitted successfully!');
            return $this->redirectToRoute('app_reviews');
        }
        
        return $this->render('review/new.html.twig', [
            'session' => $session,
            'reviewed_user' => $reviewedUser,
        ]);
    }
    
    #[Route('/{id}', name: 'app_review_show')]
    public function show(string $id, ReviewRepository $reviewRepository, UserRepository $userRepository): Response
    {
        $review = $reviewRepository->find($id);
        
        if (!$review) {
            throw $this->createNotFoundException('Review not found');
        }
        
        // Get user objects for the reviewer and reviewed user
        $reviewer = $userRepository->find($review->getReviewerId());
        $reviewedUser = $userRepository->find($review->getReviewedUserId());
        
        return $this->render('review/show.html.twig', [
            'review' => $review,
            'reviewer' => $reviewer,
            'reviewed_user' => $reviewedUser,
        ]);
    }
    
    #[Route('/{id}/delete', name: 'app_review_delete', methods: ['POST'])]
    public function delete(string $id, Request $request, ReviewRepository $reviewRepository): Response
    {
        // Ensure user is admin or the author of the review
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $review = $reviewRepository->find($id);
        
        if (!$review) {
            throw $this->createNotFoundException('Review not found');
        }
        
        $currentUser = $this->getUser();
        
        // Check if the current user is the author of the review or an admin
        if ($review->getReviewerId() !== $currentUser->getId() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You do not have permission to delete this review');
        }
        
        // CSRF protection
        if (!$this->isCsrfTokenValid('delete'.$review->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }
        
        // Delete the review
        $reviewRepository->remove($review);
        
        $this->addFlash('success', 'Review deleted successfully.');
        
        return $this->redirectToRoute('app_reviews');
    }
} 
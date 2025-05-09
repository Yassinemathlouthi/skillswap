<?php

namespace App\Controller;

use App\Service\UserMatchingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/matching')]
class MatchingController extends AbstractController
{
    private UserMatchingService $matchingService;

    public function __construct(UserMatchingService $matchingService)
    {
        $this->matchingService = $matchingService;
    }

    #[Route('/', name: 'app_matching')]
    public function index(): Response
    {
        // Ensure user is logged in
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();
        
        // Get all matching types
        $perfectMatches = $this->matchingService->findPerfectMatches($user, 4);
        $potentialTeachers = $this->matchingService->findTeachersForUser($user, 4);
        $potentialStudents = $this->matchingService->findStudentsForUser($user, 4);
        
        return $this->render('matching/index.html.twig', [
            'perfectMatches' => $perfectMatches,
            'potentialTeachers' => $potentialTeachers,
            'potentialStudents' => $potentialStudents,
        ]);
    }

    #[Route('/perfect-matches', name: 'app_matching_perfect')]
    public function perfectMatches(): Response
    {
        // Ensure user is logged in
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();
        
        // Get perfect matches (users who can both teach and learn from the current user)
        $perfectMatches = $this->matchingService->findPerfectMatches($user, 20);
        
        return $this->render('matching/perfect_matches.html.twig', [
            'perfectMatches' => $perfectMatches,
        ]);
    }

    #[Route('/find-teachers', name: 'app_matching_teachers')]
    public function findTeachers(): Response
    {
        // Ensure user is logged in
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();
        
        // Get potential teachers (users who can teach the current user's wanted skills)
        $potentialTeachers = $this->matchingService->findTeachersForUser($user, 20);
        
        return $this->render('matching/teachers.html.twig', [
            'potentialTeachers' => $potentialTeachers,
        ]);
    }

    #[Route('/find-students', name: 'app_matching_students')]
    public function findStudents(): Response
    {
        // Ensure user is logged in
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();
        
        // Get potential students (users who want to learn the current user's offered skills)
        $potentialStudents = $this->matchingService->findStudentsForUser($user, 20);
        
        return $this->render('matching/students.html.twig', [
            'potentialStudents' => $potentialStudents,
        ]);
    }
} 
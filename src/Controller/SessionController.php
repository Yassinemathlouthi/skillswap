<?php

namespace App\Controller;

use App\Entity\Session;
use App\Entity\User;
use App\Entity\Skill;
use App\Form\SessionType;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use App\Repository\SkillRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\GeocodingService;
use App\Service\CalendarService;

#[Route('/sessions')]
class SessionController extends AbstractController
{
    #[Route('/', name: 'app_sessions')]
    public function index(SessionRepository $sessionRepository): Response
    {
        // Ensure user is logged in
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        /** @var User $user */
        $user = $this->getUser();
        
        // Get all sessions where the current user is involved
        $sessions = $sessionRepository->findByUser($user);
        
        // Split sessions into upcoming and past
        $upcomingSessions = [];
        $pastSessions = [];
        $now = new \DateTime();
        
        foreach ($sessions as $session) {
            if ($session->getDateTime() > $now) {
                $upcomingSessions[] = $session;
            } else {
                $pastSessions[] = $session;
            }
        }
        
        // Sort upcoming sessions by date (nearest first)
        usort($upcomingSessions, function($a, $b) {
            return $a->getDateTime() <=> $b->getDateTime();
        });
        
        // Sort past sessions by date (most recent first)
        usort($pastSessions, function($a, $b) {
            return $b->getDateTime() <=> $a->getDateTime();
        });
        
        return $this->render('session/index.html.twig', [
            'upcoming_sessions' => $upcomingSessions,
            'past_sessions' => $pastSessions,
        ]);
    }
    
    #[Route('/new/{username}', name: 'app_session_new')]
    public function new(string $username, Request $request, UserRepository $userRepository, SkillRepository $skillRepository, SessionRepository $sessionRepository, GeocodingService $geocodingService): Response
    {
        // Ensure user is logged in
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $otherUser = $userRepository->findByUsername($username);
        
        if (!$otherUser) {
            throw $this->createNotFoundException('User not found');
        }
        
        // Check if the user is trying to book a session with themselves
        if ($currentUser->getId() === $otherUser->getId()) {
            $this->addFlash('error', 'You cannot book a session with yourself.');
            return $this->redirectToRoute('app_skills');
        }
        
        // Create a new session
        $session = new Session();
        $session->setFromUser($currentUser);
        $session->setToUser($otherUser);
        $session->setStatus(Session::STATUS_PENDING);
        
        // Set default location based on user preferences if available
        if ($currentUser->getLatitude() && $currentUser->getLongitude()) {
            $session->setLatitude($currentUser->getLatitude());
            $session->setLongitude($currentUser->getLongitude());
            $session->setLocation($currentUser->getLocation());
        }
        
        // Create and handle the form
        $form = $this->createForm(SessionType::class, $session, [
            'current_user' => $currentUser,
            'other_user' => $otherUser,
        ]);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Check if a location string was provided and geocode it
            $location = $session->getLocation();
            if ($location && (!$session->getLatitude() || !$session->getLongitude())) {
                try {
                    $coordinates = $geocodingService->geocodeAddress($location);
                    if ($coordinates) {
                        $session->setLatitude($coordinates['latitude']);
                        $session->setLongitude($coordinates['longitude']);
                    }
                } catch (\Exception $e) {
                    // If geocoding fails, continue without coordinates
                }
            }
            
            $sessionRepository->save($session, true);
            
            $this->addFlash('success', 'Session request sent successfully! You\'ll be notified when the user responds.');
            
            return $this->redirectToRoute('app_sessions');
        }
        
        return $this->render('session/new.html.twig', [
            'form' => $form->createView(),
            'other_user' => $otherUser,
        ]);
    }
    
    #[Route('/{id}', name: 'app_session_view', requirements: ['id' => '\d+'])]
    public function view(int $id, SessionRepository $sessionRepository): Response
    {
        // Ensure user is logged in
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $session = $sessionRepository->find($id);
        
        if (!$session) {
            throw $this->createNotFoundException('Session not found');
        }
        
        // Ensure the current user is part of this session
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        if ($session->getFromUser()->getId() !== $currentUser->getId() && $session->getToUser()->getId() !== $currentUser->getId()) {
            throw $this->createAccessDeniedException('You do not have access to this session');
        }
        
        return $this->render('session/view.html.twig', [
            'session' => $session,
            'current_user' => $currentUser,
        ]);
    }
    
    #[Route('/{id}/status/{status}', name: 'app_session_update_status')]
    public function updateStatus(int $id, string $status, SessionRepository $sessionRepository): Response
    {
        // Ensure user is logged in
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $session = $sessionRepository->find($id);
        
        if (!$session) {
            throw $this->createNotFoundException('Session not found');
        }
        
        // Ensure the current user is the recipient of the session request
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        if ($session->getToUser()->getId() !== $currentUser->getId()) {
            throw $this->createAccessDeniedException('You do not have permission to update this session');
        }
        
        // Validate the status
        $allowedStatuses = [Session::STATUS_CONFIRMED, Session::STATUS_CANCELED, Session::STATUS_COMPLETED];
        if (!in_array($status, $allowedStatuses)) {
            throw $this->createNotFoundException('Invalid status');
        }
        
        // Update the session status
        $session->setStatus($status);
        $session->setUpdatedAt(new \DateTime());
        $sessionRepository->save($session, true);
        
        // Set appropriate flash message
        if ($status === Session::STATUS_CONFIRMED) {
            $this->addFlash('success', 'Session confirmed! It has been added to your calendar.');
        } elseif ($status === Session::STATUS_CANCELED) {
            $this->addFlash('info', 'Session has been canceled.');
        } elseif ($status === Session::STATUS_COMPLETED) {
            $this->addFlash('success', 'Session marked as completed. Don\'t forget to leave a review!');
        }
        
        return $this->redirectToRoute('app_session_view', ['id' => $id]);
    }
    
    #[Route('/{id}/cancel', name: 'app_session_cancel')]
    public function cancelSession(int $id, SessionRepository $sessionRepository): Response
    {
        // Ensure user is logged in
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $session = $sessionRepository->find($id);
        
        if (!$session) {
            throw $this->createNotFoundException('Session not found');
        }
        
        // Ensure the current user is part of this session
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        if ($session->getFromUser()->getId() !== $currentUser->getId() && $session->getToUser()->getId() !== $currentUser->getId()) {
            throw $this->createAccessDeniedException('You do not have access to this session');
        }
        
        // Only allow cancellation of pending or confirmed sessions
        if (!in_array($session->getStatus(), [Session::STATUS_PENDING, Session::STATUS_CONFIRMED])) {
            $this->addFlash('error', 'Only pending or confirmed sessions can be canceled.');
            return $this->redirectToRoute('app_session_view', ['id' => $id]);
        }
        
        // Update the session status
        $session->setStatus(Session::STATUS_CANCELED);
        $session->setUpdatedAt(new \DateTime());
        $sessionRepository->save($session, true);
        
        $this->addFlash('info', 'Session has been canceled.');
        
        return $this->redirectToRoute('app_sessions');
    }
    
    #[Route('/{id}/calendar', name: 'app_session_calendar')]
    public function calendar(int $id, SessionRepository $sessionRepository, CalendarService $calendarService): Response
    {
        // Ensure user is logged in
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $session = $sessionRepository->find($id);
        
        if (!$session) {
            throw $this->createNotFoundException('Session not found');
        }
        
        // Ensure the current user is part of this session
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        if ($session->getFromUser()->getId() !== $currentUser->getId() && $session->getToUser()->getId() !== $currentUser->getId()) {
            throw $this->createAccessDeniedException('You do not have access to this session');
        }
        
        // Only confirmed sessions can be added to calendar
        if ($session->getStatus() !== Session::STATUS_CONFIRMED) {
            $this->addFlash('warning', 'Only confirmed sessions can be added to your calendar.');
            return $this->redirectToRoute('app_session_view', ['id' => $id]);
        }
        
        $googleCalendarUrl = $calendarService->generateGoogleCalendarUrl($session);
        
        return $this->render('session/calendar.html.twig', [
            'session' => $session,
            'current_user' => $currentUser,
            'google_calendar_url' => $googleCalendarUrl,
        ]);
    }
} 
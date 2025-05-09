<?php

namespace App\Controller;

use App\Entity\Session;
use App\Entity\User;
use App\Repository\SessionRepository;
use App\Service\CalendarService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/calendar')]
class CalendarController extends AbstractController
{
    #[Route('/', name: 'app_calendar')]
    public function index(SessionRepository $sessionRepository): Response
    {
        // Ensure user is logged in
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        /** @var User $user */
        $user = $this->getUser();
        
        // Get upcoming sessions
        $sessions = $sessionRepository->findUpcomingSessionsByUser($user);
        
        return $this->render('calendar/index.html.twig', [
            'sessions' => $sessions,
        ]);
    }
    
    #[Route('/month/{year}/{month}', name: 'app_calendar_month')]
    public function month(int $year, int $month, Request $request, SessionRepository $sessionRepository): Response
    {
        // Ensure user is logged in
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        /** @var User $user */
        $user = $this->getUser();
        
        // Validate month and year
        if ($month < 1 || $month > 12) {
            throw $this->createNotFoundException('Invalid month');
        }
        
        if ($year < 2000 || $year > 2100) {
            throw $this->createNotFoundException('Invalid year');
        }
        
        // Create date range for the requested month
        $startDate = new \DateTime($year . '-' . $month . '-01');
        $endDate = clone $startDate;
        $endDate->modify('last day of this month');
        $endDate->setTime(23, 59, 59);
        
        // Get sessions for the month
        $sessions = $sessionRepository->findSessionsBetweenDates($user, $startDate, $endDate);
        
        // Build calendar data
        $calendarData = [];
        $daysInMonth = (int)$endDate->format('d');
        $firstDayWeekday = (int)$startDate->format('N'); // 1 (Monday) to 7 (Sunday)
        
        // Previous month
        $prevMonth = clone $startDate;
        $prevMonth->modify('-1 month');
        
        // Next month
        $nextMonth = clone $startDate;
        $nextMonth->modify('+1 month');
        
        // Group sessions by day
        $sessionsByDay = [];
        foreach ($sessions as $session) {
            $day = (int)$session->getDateTime()->format('d');
            if (!isset($sessionsByDay[$day])) {
                $sessionsByDay[$day] = [];
            }
            $sessionsByDay[$day][] = $session;
        }
        
        return $this->render('calendar/month.html.twig', [
            'year' => $year,
            'month' => $month,
            'monthName' => $startDate->format('F'),
            'daysInMonth' => $daysInMonth,
            'firstDayWeekday' => $firstDayWeekday,
            'sessions' => $sessions,
            'sessionsByDay' => $sessionsByDay,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
        ]);
    }
    
    #[Route('/day/{year}/{month}/{day}', name: 'app_calendar_day')]
    public function day(int $year, int $month, int $day, SessionRepository $sessionRepository): Response
    {
        // Ensure user is logged in
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        /** @var User $user */
        $user = $this->getUser();
        
        // Validate date
        if (!checkdate($month, $day, $year)) {
            throw $this->createNotFoundException('Invalid date');
        }
        
        // Create date range for the requested day
        $startDate = new \DateTime($year . '-' . $month . '-' . $day);
        $startDate->setTime(0, 0, 0);
        
        $endDate = clone $startDate;
        $endDate->setTime(23, 59, 59);
        
        // Get sessions for the day
        $sessions = $sessionRepository->findSessionsBetweenDates($user, $startDate, $endDate);
        
        // Previous and next day
        $prevDay = clone $startDate;
        $prevDay->modify('-1 day');
        
        $nextDay = clone $startDate;
        $nextDay->modify('+1 day');
        
        return $this->render('calendar/day.html.twig', [
            'date' => $startDate,
            'sessions' => $sessions,
            'prevDay' => $prevDay,
            'nextDay' => $nextDay,
        ]);
    }
    
    #[Route('/ics/{id}', name: 'app_calendar_session_ics')]
    public function downloadSessionIcs(Session $session, CalendarService $calendarService): Response
    {
        // Ensure user is logged in and part of the session
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        
        if ($session->getFromUser()->getId() !== $currentUser->getId() && $session->getToUser()->getId() !== $currentUser->getId()) {
            throw $this->createAccessDeniedException('You do not have access to this session');
        }
        
        $icsContent = $calendarService->generateIcsForSession($session);
        
        $response = new Response($icsContent);
        $response->headers->set('Content-Type', 'text/calendar; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="skillswap_session_' . $session->getId() . '.ics"');
        
        return $response;
    }
    
    #[Route('/ics', name: 'app_calendar_all_ics')]
    public function downloadAllIcs(SessionRepository $sessionRepository, CalendarService $calendarService): Response
    {
        // Ensure user is logged in
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        /** @var User $user */
        $user = $this->getUser();
        
        // Get upcoming sessions
        $sessions = $sessionRepository->findUpcomingSessionsByUser($user);
        
        $icsContent = $calendarService->generateIcsForSessions($sessions);
        
        $response = new Response($icsContent);
        $response->headers->set('Content-Type', 'text/calendar; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="skillswap_calendar.ics"');
        
        return $response;
    }
} 
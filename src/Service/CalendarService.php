<?php

namespace App\Service;

use App\Entity\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CalendarService
{
    private $urlGenerator;
    
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }
    
    /**
     * Generate an ICS file content for a single session
     */
    public function generateIcsForSession(Session $session): string
    {
        $startTime = $session->getDateTime()->format('Ymd\THis\Z');
        $endTime = $session->getEndTime() ? $session->getEndTime()->format('Ymd\THis\Z') : null;
        
        if (!$endTime) {
            $endDateTime = clone $session->getDateTime();
            $endDateTime->modify('+1 hour');
            $endTime = $endDateTime->format('Ymd\THis\Z');
        }
        
        $now = new \DateTime();
        $created = $now->format('Ymd\THis\Z');
        
        $uid = md5($session->getId() . $session->getFromUser()->getId() . $session->getToUser()->getId() . $startTime);
        
        $summary = 'SkillSwap: ';
        if ($session->getSkill()) {
            $summary .= $session->getSkill()->getName() . ' session';
        } else {
            $summary .= 'Skill Exchange Session';
        }
        
        $description = "Skill Exchange Session\n\n";
        $description .= "With: " . $session->getFromUser()->getUsername() . " and " . $session->getToUser()->getUsername() . "\n";
        if ($session->getNotes()) {
            $description .= "Notes: " . $session->getNotes() . "\n";
        }
        
        $location = $session->getLocation() ?: '';
        
        $url = $this->urlGenerator->generate('app_session_view', ['id' => $session->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        
        $ics = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//SkillSwap//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:' . $uid,
            'DTSTAMP:' . $created,
            'DTSTART:' . $startTime,
            'DTEND:' . $endTime,
            'SUMMARY:' . $this->escapeIcsText($summary),
            'DESCRIPTION:' . $this->escapeIcsText($description),
            'LOCATION:' . $this->escapeIcsText($location),
            'URL:' . $url,
            'STATUS:CONFIRMED',
            'END:VEVENT',
            'END:VCALENDAR'
        ];
        
        return implode("\r\n", $ics);
    }
    
    /**
     * Generate an ICS file content for multiple sessions
     */
    public function generateIcsForSessions(array $sessions): string
    {
        $ics = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//SkillSwap//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH'
        ];
        
        foreach ($sessions as $session) {
            $startTime = $session->getDateTime()->format('Ymd\THis\Z');
            $endTime = $session->getEndTime() ? $session->getEndTime()->format('Ymd\THis\Z') : null;
            
            if (!$endTime) {
                $endDateTime = clone $session->getDateTime();
                $endDateTime->modify('+1 hour');
                $endTime = $endDateTime->format('Ymd\THis\Z');
            }
            
            $now = new \DateTime();
            $created = $now->format('Ymd\THis\Z');
            
            $uid = md5($session->getId() . $session->getFromUser()->getId() . $session->getToUser()->getId() . $startTime);
            
            $summary = 'SkillSwap: ';
            if ($session->getSkill()) {
                $summary .= $session->getSkill()->getName() . ' session';
            } else {
                $summary .= 'Skill Exchange Session';
            }
            
            $description = "Skill Exchange Session\n\n";
            $description .= "With: " . $session->getFromUser()->getUsername() . " and " . $session->getToUser()->getUsername() . "\n";
            if ($session->getNotes()) {
                $description .= "Notes: " . $session->getNotes() . "\n";
            }
            
            $location = $session->getLocation() ?: '';
            
            $url = $this->urlGenerator->generate('app_session_view', ['id' => $session->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
            
            $ics[] = 'BEGIN:VEVENT';
            $ics[] = 'UID:' . $uid;
            $ics[] = 'DTSTAMP:' . $created;
            $ics[] = 'DTSTART:' . $startTime;
            $ics[] = 'DTEND:' . $endTime;
            $ics[] = 'SUMMARY:' . $this->escapeIcsText($summary);
            $ics[] = 'DESCRIPTION:' . $this->escapeIcsText($description);
            $ics[] = 'LOCATION:' . $this->escapeIcsText($location);
            $ics[] = 'URL:' . $url;
            $ics[] = 'STATUS:CONFIRMED';
            $ics[] = 'END:VEVENT';
        }
        
        $ics[] = 'END:VCALENDAR';
        
        return implode("\r\n", $ics);
    }
    
    /**
     * Generate Google Calendar add event URL
     */
    public function generateGoogleCalendarUrl(Session $session): string
    {
        $startTime = $session->getDateTime()->format('Ymd\THis\Z');
        $endTime = $session->getEndTime() ? $session->getEndTime()->format('Ymd\THis\Z') : null;
        
        if (!$endTime) {
            $endDateTime = clone $session->getDateTime();
            $endDateTime->modify('+1 hour');
            $endTime = $endDateTime->format('Ymd\THis\Z');
        }
        
        $summary = 'SkillSwap: ';
        if ($session->getSkill()) {
            $summary .= $session->getSkill()->getName() . ' session';
        } else {
            $summary .= 'Skill Exchange Session';
        }
        
        $description = "Skill Exchange Session\n\n";
        $description .= "With: " . $session->getFromUser()->getUsername() . " and " . $session->getToUser()->getUsername() . "\n";
        if ($session->getNotes()) {
            $description .= "Notes: " . $session->getNotes() . "\n";
        }
        
        $location = $session->getLocation() ?: '';
        
        $url = 'https://www.google.com/calendar/render?action=TEMPLATE';
        $url .= '&text=' . urlencode($summary);
        $url .= '&dates=' . $startTime . '/' . $endTime;
        $url .= '&details=' . urlencode($description);
        $url .= '&location=' . urlencode($location);
        
        return $url;
    }
    
    /**
     * Escape special characters in ICS text
     */
    private function escapeIcsText(string $text): string
    {
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace(',', '\,', $text);
        $text = str_replace(';', '\;', $text);
        $text = str_replace("\n", '\n', $text);
        
        // Wrap lines longer than 75 characters
        $text = wordwrap($text, 75, "\r\n ", true);
        
        return $text;
    }
} 
<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Skill;
use App\Repository\UserRepository;
use App\Repository\SkillRepository;
use App\Service\GeocodingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/nearby')]
class NearbyController extends AbstractController
{
    #[Route('/', name: 'app_nearby')]
    public function index(Request $request, UserRepository $userRepository, SkillRepository $skillRepository, GeocodingService $geocodingService): Response
    {
        // Ensure user is logged in
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        
        // Get user's location
        $latitude = $currentUser->getLatitude();
        $longitude = $currentUser->getLongitude();
        
        // If user doesn't have coordinates yet, show a message to update profile
        if (!$latitude || !$longitude) {
            return $this->render('nearby/index.html.twig', [
                'no_location' => true,
                'users' => [],
                'skills' => $skillRepository->findAll(),
                'selectedSkills' => [],
                'radius' => 50,
            ]);
        }
        
        // Process filters
        $radius = $request->query->getInt('radius', 50); // Default radius is 50km
        $skillIds = $request->query->all('skills');
        $selectedSkills = [];
        
        if (!empty($skillIds)) {
            $selectedSkills = $skillRepository->findBy(['id' => $skillIds]);
            $users = $userRepository->findUsersWithSkillsNearby($skillIds, $latitude, $longitude, $radius);
        } else {
            $users = $userRepository->findUsersNearby($latitude, $longitude, $radius);
        }
        
        // Remove current user from results
        $users = array_filter($users, function($userWithDistance) use ($currentUser) {
            return $userWithDistance[0]->getId() !== $currentUser->getId();
        });
        
        return $this->render('nearby/index.html.twig', [
            'users' => $users,
            'skills' => $skillRepository->findAll(),
            'selectedSkills' => $selectedSkills,
            'radius' => $radius,
            'no_location' => false,
        ]);
    }
    
    #[Route('/update-location', name: 'app_nearby_update_location', methods: ['POST'])]
    public function updateLocation(Request $request, GeocodingService $geocodingService, UserRepository $userRepository): Response
    {
        // Ensure user is logged in
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        /** @var User $user */
        $user = $this->getUser();
        
        $address = $request->request->get('address');
        
        if (!$address) {
            $this->addFlash('error', 'Please enter an address.');
            return $this->redirectToRoute('app_nearby');
        }
        
        $coordinates = $geocodingService->geocodeAddress($address);
        
        if (!$coordinates) {
            $this->addFlash('error', 'Could not find coordinates for this address. Please try a different address.');
            return $this->redirectToRoute('app_nearby');
        }
        
        // Update user location data
        $user->setLocation($address);
        $user->setLatitude($coordinates['latitude']);
        $user->setLongitude($coordinates['longitude']);
        
        $userRepository->save($user, true);
        
        $this->addFlash('success', 'Your location has been updated successfully!');
        
        return $this->redirectToRoute('app_nearby');
    }
    
    #[Route('/map', name: 'app_nearby_map')]
    public function map(Request $request, UserRepository $userRepository, SkillRepository $skillRepository): Response
    {
        // Ensure user is logged in
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        
        // Get user's location
        $latitude = $currentUser->getLatitude();
        $longitude = $currentUser->getLongitude();
        
        // If user doesn't have coordinates yet, redirect to nearby page
        if (!$latitude || !$longitude) {
            $this->addFlash('info', 'Please update your location first to view the map.');
            return $this->redirectToRoute('app_nearby');
        }
        
        // Process filters
        $radius = $request->query->getInt('radius', 50); // Default radius is 50km
        $skillIds = $request->query->all('skills');
        $selectedSkills = [];
        
        if (!empty($skillIds)) {
            $selectedSkills = $skillRepository->findBy(['id' => $skillIds]);
            $users = $userRepository->findUsersWithSkillsNearby($skillIds, $latitude, $longitude, $radius);
        } else {
            $users = $userRepository->findUsersNearby($latitude, $longitude, $radius);
        }
        
        // Remove current user from results
        $users = array_filter($users, function($userWithDistance) use ($currentUser) {
            return $userWithDistance[0]->getId() !== $currentUser->getId();
        });
        
        return $this->render('nearby/map.html.twig', [
            'users' => $users,
            'currentUser' => $currentUser,
            'skills' => $skillRepository->findAll(),
            'selectedSkills' => $selectedSkills,
            'radius' => $radius,
        ]);
    }
} 
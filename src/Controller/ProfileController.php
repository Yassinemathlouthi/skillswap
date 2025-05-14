<?php

namespace App\Controller;

use App\Entity\UserAvailability;
use App\Entity\UserSkillOffered;
use App\Entity\UserSkillWanted;
use App\Repository\SkillRepository;
use App\Repository\UserRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserAvailabilityRepository;
use App\Repository\UserSkillOfferedRepository;
use App\Repository\UserSkillWantedRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(): Response
    {
        // Ensure user is logged in
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        return $this->render('profile/index.html.twig', [
            'user' => $this->getUser(),
        ]);
    }
    
    #[Route('/profile/edit', name: 'app_profile_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, SkillRepository $skillRepository): Response
    {
        // Ensure user is logged in
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $user = $this->getUser();
        
        // Process form submissions based on the form type (determined by which tab is active)
        $formType = $request->request->get('form_type');
        
        if ($request->isMethod('POST')) {
            // Personal Info Form
            if ($formType === 'personal_info') {
                $firstName = $request->request->get('firstName');
                $lastName = $request->request->get('lastName');
                $location = $request->request->get('location');
                $bio = $request->request->get('bio');
                
                if ($firstName !== null) {
                    $user->setFirstName($firstName);
                }
                
                if ($lastName !== null) {
                    $user->setLastName($lastName);
                }
                
                if ($location !== null) {
                    $user->setLocation($location);
                }
                
                if ($bio !== null) {
                    $user->setBio($bio);
                }
                
                // Handle profile image upload
                $profileImage = $request->files->get('profileImage');
                if ($profileImage) {
                    $originalFilename = pathinfo($profileImage->getClientOriginalName(), PATHINFO_FILENAME);
                    // Generate a safe filename using a simpler method that doesn't require intl extension
                    $safeFilename = preg_replace('/[^A-Za-z0-9_]/', '', $originalFilename);
                    $safeFilename = strtolower($safeFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$profileImage->guessExtension();
                    
                    // Move the file to the directory where profile images are stored
                    try {
                        $uploadsDirectory = $this->getParameter('kernel.project_dir').'/public/uploads/profiles';
                        
                        // Create directory if it doesn't exist
                        if (!is_dir($uploadsDirectory)) {
                            mkdir($uploadsDirectory, 0777, true);
                        }
                        
                        $profileImage->move(
                            $uploadsDirectory,
                            $newFilename
                        );
                        
                        // Store the path to the image in the user entity
                        $user->setAvatar('uploads/profiles/'.$newFilename);
                    } catch (\Exception $e) {
                        $this->addFlash('danger', 'Error uploading profile image: ' . $e->getMessage());
                    }
                }
                
                $user->setUpdatedAt(new \DateTime());
                $entityManager->flush();
                
                $this->addFlash('success', 'Your personal information has been updated.');
                
                return $this->redirectToRoute('app_profile_edit');
            }
            // Skills Offered Form
            elseif ($formType === 'skill_offered') {
                $skillId = $request->request->get('skillOffered');
                
                if ($skillId) {
                    $skill = $skillRepository->find($skillId);
                    
                    if ($skill) {
                        // Check if user already has this skill
                        $hasSkill = false;
                        foreach ($user->getSkillsOffered() as $userSkill) {
                            if ($userSkill->getSkill()->getId() == $skill->getId()) {
                                $hasSkill = true;
                                break;
                            }
                        }
                        
                        if (!$hasSkill) {
                            $userSkillOffered = new UserSkillOffered();
                            $userSkillOffered->setUser($user);
                            $userSkillOffered->setSkill($skill);
                            
                            $entityManager->persist($userSkillOffered);
                            $entityManager->flush();
                            
                            $this->addFlash('success', 'Skill has been added to your offered skills.');
                        } else {
                            $this->addFlash('warning', 'You already have this skill in your offered skills.');
                        }
                    }
                }
                
                return $this->redirectToRoute('app_profile_edit', ['_fragment' => 'skills']);
            }
            // Skills Wanted Form
            elseif ($formType === 'skill_wanted') {
                $skillId = $request->request->get('skillWanted');
                
                if ($skillId) {
                    $skill = $skillRepository->find($skillId);
                    
                    if ($skill) {
                        // Check if user already has this skill
                        $hasSkill = false;
                        foreach ($user->getSkillsWanted() as $userSkill) {
                            if ($userSkill->getSkill()->getId() == $skill->getId()) {
                                $hasSkill = true;
                                break;
                            }
                        }
                        
                        if (!$hasSkill) {
                            $userSkillWanted = new UserSkillWanted();
                            $userSkillWanted->setUser($user);
                            $userSkillWanted->setSkill($skill);
                            
                            $entityManager->persist($userSkillWanted);
                            $entityManager->flush();
                            
                            $this->addFlash('success', 'Skill has been added to your wanted skills.');
                        } else {
                            $this->addFlash('warning', 'You already have this skill in your wanted skills.');
                        }
                    }
                }
                
                return $this->redirectToRoute('app_profile_edit', ['_fragment' => 'skills']);
            }
            // Availability Form
            elseif ($formType === 'availability') {
                $dayOfWeek = (int)$request->request->get('day');
                $startTime = $request->request->get('startTime');
                $endTime = $request->request->get('endTime');
                
                if ($dayOfWeek && $startTime && $endTime) {
                    $startDateTime = new \DateTime($startTime);
                    $endDateTime = new \DateTime($endTime);
                    
                    $availability = new UserAvailability();
                    $availability->setUser($user);
                    $availability->setDayOfWeek($dayOfWeek);
                    $availability->setStartTime($startDateTime);
                    $availability->setEndTime($endDateTime);
                    
                    $entityManager->persist($availability);
                    $entityManager->flush();
                    
                    $this->addFlash('success', 'Availability slot has been added.');
                }
                
                return $this->redirectToRoute('app_profile_edit', ['_fragment' => 'availability']);
            }
            // Password Change Form
            elseif ($formType === 'change_password') {
                // Password change logic would go here
                // This typically involves password encoding, validation, etc.
                $this->addFlash('info', 'Password change functionality will be implemented soon.');
                
                return $this->redirectToRoute('app_profile_edit', ['_fragment' => 'account']);
            }
        }

        // Get all skills for dropdowns
        $allSkills = $skillRepository->findAll();
        
        return $this->render('profile/edit.html.twig', [
            'user' => $user,
            'skills' => $allSkills
        ]);
    }
    
    #[Route('/profile/remove-skill-offered/{id}', name: 'app_profile_remove_skill_offered')]
    public function removeSkillOffered(UserSkillOffered $userSkillOffered, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        // Check if this skill belongs to the current user
        if ($userSkillOffered->getUser() === $this->getUser()) {
            $entityManager->remove($userSkillOffered);
            $entityManager->flush();
            
            $this->addFlash('success', 'Skill has been removed from your offered skills.');
        } else {
            $this->addFlash('danger', 'You do not have permission to remove this skill.');
        }
        
        return $this->redirectToRoute('app_profile_edit', ['_fragment' => 'skills']);
    }
    
    #[Route('/profile/remove-skill-wanted/{id}', name: 'app_profile_remove_skill_wanted')]
    public function removeSkillWanted(UserSkillWanted $userSkillWanted, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        // Check if this skill belongs to the current user
        if ($userSkillWanted->getUser() === $this->getUser()) {
            $entityManager->remove($userSkillWanted);
            $entityManager->flush();
            
            $this->addFlash('success', 'Skill has been removed from your wanted skills.');
        } else {
            $this->addFlash('danger', 'You do not have permission to remove this skill.');
        }
        
        return $this->redirectToRoute('app_profile_edit', ['_fragment' => 'skills']);
    }
    
    #[Route('/profile/remove-availability/{id}', name: 'app_profile_remove_availability')]
    public function removeAvailability(UserAvailability $availability, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        // Check if this availability belongs to the current user
        if ($availability->getUser() === $this->getUser()) {
            $entityManager->remove($availability);
            $entityManager->flush();
            
            $this->addFlash('success', 'Availability slot has been removed.');
        } else {
            $this->addFlash('danger', 'You do not have permission to remove this availability slot.');
        }
        
        return $this->redirectToRoute('app_profile_edit', ['_fragment' => 'availability']);
    }
    
    #[Route('/user/{username}', name: 'app_user_profile')]
    public function userProfile(string $username, UserRepository $userRepository, ReviewRepository $reviewRepository): Response
    {
        $user = $userRepository->findOneBy(['username' => $username]);
        
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }
        
        // Get reviews for this user
        $reviews = $reviewRepository->findBy(['reviewedUser' => $user]);
        
        // Calculate average rating
        $averageRating = 0;
        if (count($reviews) > 0) {
            $totalRating = 0;
            foreach ($reviews as $review) {
                $totalRating += $review->getRating();
            }
            $averageRating = $totalRating / count($reviews);
        }
        
        return $this->render('profile/user.html.twig', [
            'user' => $user,
            'reviews' => $reviews,
            'average_rating' => $averageRating,
        ]);
    }
} 
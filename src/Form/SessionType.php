<?php

namespace App\Form;

use App\Entity\Session;
use App\Entity\Skill;
use App\Entity\User;
use App\Repository\SkillRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\GreaterThan;

class SessionType extends AbstractType
{
    private $skillRepository;
    
    public function __construct(SkillRepository $skillRepository)
    {
        $this->skillRepository = $skillRepository;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $currentUser */
        $currentUser = $options['current_user'];
        
        /** @var User $otherUser */
        $otherUser = $options['other_user'];
        
        // Get all skills
        $allSkills = $this->skillRepository->findAll();
        
        // Mark skills that are relevant as preferred
        $matchedSkills = [];
        
        // Get skills that the other user offers and current user wants
        foreach ($otherUser->getSkillsOffered() as $skillOffered) {
            foreach ($currentUser->getSkillsWanted() as $skillWanted) {
                if ($skillOffered->getSkill()->getId() === $skillWanted->getSkill()->getId()) {
                    $matchedSkills[$skillOffered->getSkill()->getId()] = true;
                    break;
                }
            }
        }
        
        // Get skills that the current user offers and other user wants
        foreach ($currentUser->getSkillsOffered() as $skillOffered) {
            foreach ($otherUser->getSkillsWanted() as $skillWanted) {
                if ($skillOffered->getSkill()->getId() === $skillWanted->getSkill()->getId()) {
                    $matchedSkills[$skillOffered->getSkill()->getId()] = true;
                    break;
                }
            }
        }
        
        $builder
            ->add('dateTime', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date and Time',
                'required' => true,
                'attr' => [
                    'min' => (new \DateTime())->format('Y-m-d\TH:i'),
                    'class' => 'form-control'
                ]
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Duration (minutes)',
                'required' => false,
                'attr' => [
                    'min' => 15,
                    'max' => 480,
                    'step' => 15,
                    'placeholder' => 'e.g. 60 minutes',
                    'class' => 'form-control'
                ],
                'data' => 60, // Default 60 minutes
            ])
            ->add('location', TextType::class, [
                'label' => 'Location (optional)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Enter meeting location or "Online"',
                    'class' => 'form-control'
                ]
            ])
            ->add('skill', EntityType::class, [
                'class' => Skill::class,
                'choice_label' => function(Skill $skill) use ($matchedSkills) {
                    return array_key_exists($skill->getId(), $matchedSkills) 
                        ? '✓ ' . $skill->getName() . ' (Good match!)'
                        : $skill->getName();
                },
                'label' => 'Skill to Exchange',
                'placeholder' => 'Select a skill',
                'choices' => $allSkills,
                'choice_attr' => function(Skill $skill) use ($matchedSkills) {
                    return array_key_exists($skill->getId(), $matchedSkills) 
                        ? ['class' => 'preferred-skill', 'style' => 'font-weight: bold; color: #28a745;']
                        : [];
                },
                'group_by' => function(Skill $skill) {
                    // If the skill has categories, use the first one for grouping
                    $categories = $skill->getSkillCategories();
                    if (!$categories->isEmpty()) {
                        return $categories->first()->getCategory()->getName();
                    }
                    
                    return 'Other Skills';
                },
                'required' => true,
                'attr' => [
                    'class' => 'form-select shadow-sm',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please select a skill']),
                ],
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Session Notes',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Add any details about what you want to learn or teach in this session...',
                    'rows' => 4,
                    'class' => 'form-control shadow-sm',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Session::class,
        ]);
        
        $resolver->setRequired(['current_user', 'other_user']);
        $resolver->setAllowedTypes('current_user', User::class);
        $resolver->setAllowedTypes('other_user', User::class);
    }
} 
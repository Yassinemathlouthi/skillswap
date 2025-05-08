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
                'label' => 'Session Date and Time',
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'min' => (new \DateTime('+1 hour'))->format('Y-m-d\TH:i'),
                    'class' => 'form-control shadow-sm',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please select a date and time']),
                    new GreaterThan([
                        'value' => new \DateTime('+30 minutes'),
                        'message' => 'The session date must be at least 30 minutes in the future'
                    ]),
                ],
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
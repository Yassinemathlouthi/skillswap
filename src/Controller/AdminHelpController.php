<?php

namespace App\Controller;

use App\Entity\Faq;
use App\Repository\FaqRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

#[Route('/admin/help')]
class AdminHelpController extends AbstractController
{
    #[Route('/faq', name: 'app_admin_faq')]
    public function index(FaqRepository $faqRepository): Response
    {
        // Ensure user is admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $faqs = $faqRepository->findAll();
        
        return $this->render('admin/help/faq_index.html.twig', [
            'faqs' => $faqs
        ]);
    }
    
    #[Route('/faq/new', name: 'app_admin_faq_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Ensure user is admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $faq = new Faq();
        
        $form = $this->createFormBuilder($faq)
            ->add('question', TextType::class, [
                'label' => 'Question',
                'attr' => ['class' => 'form-control']
            ])
            ->add('answer', TextareaType::class, [
                'label' => 'Answer',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 6
                ]
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Category',
                'choices' => [
                    'General' => 'General',
                    'Account' => 'Account',
                    'Sessions' => 'Sessions',
                    'Skills' => 'Skills',
                    'Technical' => 'Technical',
                    'Billing' => 'Billing'
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('displayOrder', IntegerType::class, [
                'label' => 'Display Order',
                'attr' => ['class' => 'form-control']
            ])
            ->add('isPublished', CheckboxType::class, [
                'label' => 'Published',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Create FAQ',
                'attr' => ['class' => 'btn btn-primary']
            ])
            ->getForm();
            
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($faq);
            $entityManager->flush();
            
            $this->addFlash('success', 'FAQ created successfully!');
            
            return $this->redirectToRoute('app_admin_faq');
        }
        
        return $this->render('admin/help/faq_form.html.twig', [
            'form' => $form->createView(),
            'faq' => $faq,
            'action' => 'new'
        ]);
    }
    
    #[Route('/faq/{id}/edit', name: 'app_admin_faq_edit')]
    public function edit(Request $request, Faq $faq, EntityManagerInterface $entityManager): Response
    {
        // Ensure user is admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $form = $this->createFormBuilder($faq)
            ->add('question', TextType::class, [
                'label' => 'Question',
                'attr' => ['class' => 'form-control']
            ])
            ->add('answer', TextareaType::class, [
                'label' => 'Answer',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 6
                ]
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Category',
                'choices' => [
                    'General' => 'General',
                    'Account' => 'Account',
                    'Sessions' => 'Sessions',
                    'Skills' => 'Skills',
                    'Technical' => 'Technical',
                    'Billing' => 'Billing'
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('displayOrder', IntegerType::class, [
                'label' => 'Display Order',
                'attr' => ['class' => 'form-control']
            ])
            ->add('isPublished', CheckboxType::class, [
                'label' => 'Published',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Update FAQ',
                'attr' => ['class' => 'btn btn-primary']
            ])
            ->getForm();
            
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $faq->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();
            
            $this->addFlash('success', 'FAQ updated successfully!');
            
            return $this->redirectToRoute('app_admin_faq');
        }
        
        return $this->render('admin/help/faq_form.html.twig', [
            'form' => $form->createView(),
            'faq' => $faq,
            'action' => 'edit'
        ]);
    }
    
    #[Route('/faq/{id}/delete', name: 'app_admin_faq_delete', methods: ['POST'])]
    public function delete(Request $request, Faq $faq, EntityManagerInterface $entityManager): Response
    {
        // Ensure user is admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        if ($this->isCsrfTokenValid('delete'.$faq->getId(), $request->request->get('_token'))) {
            $entityManager->remove($faq);
            $entityManager->flush();
            
            $this->addFlash('success', 'FAQ deleted successfully!');
        }
        
        return $this->redirectToRoute('app_admin_faq');
    }
} 
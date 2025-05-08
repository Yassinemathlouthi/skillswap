<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/messages')]
class MessageController extends AbstractController
{
    #[Route('/', name: 'app_messages')]
    public function index(MessageRepository $messageRepository, UserRepository $userRepository): Response
    {
        // Ensure user is logged in
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $currentUser = $this->getUser();
        
        // Get all conversations for the current user
        $conversations = [];
        $processedUsers = [];
        
        // Get all sent and received messages
        $sentMessages = $currentUser->getMessagesSent();
        $receivedMessages = $currentUser->getMessagesReceived();
        
        // Extract unique conversation partners from sent messages
        foreach ($sentMessages as $message) {
            $partnerId = $message->getReceiver()->getId();
            if (!isset($processedUsers[$partnerId]) && $partnerId !== $currentUser->getId()) {
                $processedUsers[$partnerId] = true;
                $partner = $message->getReceiver();
                
                // Find the latest message in this conversation
                $latestMessage = $messageRepository->findLatestInConversation($currentUser, $partner);
                
                // Count unread messages
                $unreadCount = $messageRepository->countUnreadMessages($currentUser, $partner);
                
                $conversations[] = [
                    'partner' => $partner,
                    'latest_message' => $latestMessage,
                    'unread_count' => $unreadCount
                ];
            }
        }
        
        // Extract unique conversation partners from received messages
        foreach ($receivedMessages as $message) {
            $partnerId = $message->getSender()->getId();
            if (!isset($processedUsers[$partnerId]) && $partnerId !== $currentUser->getId()) {
                $processedUsers[$partnerId] = true;
                $partner = $message->getSender();
                
                // Find the latest message in this conversation
                $latestMessage = $messageRepository->findLatestInConversation($currentUser, $partner);
                
                // Count unread messages
                $unreadCount = $messageRepository->countUnreadMessages($currentUser, $partner);
                
                $conversations[] = [
                    'partner' => $partner,
                    'latest_message' => $latestMessage,
                    'unread_count' => $unreadCount
                ];
            }
        }
        
        // Sort conversations by the latest message timestamp (newest first)
        usort($conversations, function($a, $b) {
            return $b['latest_message']->getTimestamp() <=> $a['latest_message']->getTimestamp();
        });
        
        return $this->render('message/index.html.twig', [
            'conversations' => $conversations,
        ]);
    }
    
    #[Route('/conversation/{username}', name: 'app_message_conversation')]
    public function conversation(string $username, MessageRepository $messageRepository, UserRepository $userRepository): Response
    {
        // Ensure user is logged in
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $otherUser = $userRepository->findByUsername($username);
        
        if (!$otherUser) {
            throw $this->createNotFoundException('User not found');
        }
        
        // Get conversation between the two users
        $messages = $messageRepository->findConversation($currentUser, $otherUser);
        
        // Mark all messages from the other user as read
        foreach ($messages as $message) {
            if ($message->getReceiver() === $currentUser && !$message->isIsRead()) {
                $messageRepository->markAsRead($message);
            }
        }
        
        return $this->render('message/conversation.html.twig', [
            'messages' => $messages,
            'other_user' => $otherUser,
            'current_user' => $currentUser,
        ]);
    }
    
    #[Route('/send', name: 'app_message_send', methods: ['POST'])]
    public function send(Request $request, UserRepository $userRepository, MessageRepository $messageRepository): Response
    {
        // Ensure user is logged in
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $receiverUsername = $request->request->get('receiver');
        $content = $request->request->get('content');
        
        // Validate input
        if (!$receiverUsername || !$content) {
            $this->addFlash('error', 'Message or recipient is missing');
            return $this->redirectToRoute('app_messages');
        }
        
        // Find the receiver
        $receiver = $userRepository->findByUsername($receiverUsername);
        if (!$receiver) {
            throw $this->createNotFoundException('Recipient not found');
        }
        
        // Create and save the message
        $message = new Message();
        $message->setSender($currentUser);
        $message->setReceiver($receiver);
        $message->setContent($content);
        $message->setIsRead(false);
        
        $messageRepository->save($message, true);
        
        // If this is an AJAX request, return JSON response
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Message sent'
            ]);
        }
        
        // Otherwise redirect to the conversation
        return $this->redirectToRoute('app_message_conversation', [
            'username' => $receiverUsername
        ]);
    }
    
    #[Route('/new/{username}', name: 'app_message_new')]
    public function new(string $username, UserRepository $userRepository): Response
    {
        // Ensure user is logged in
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $otherUser = $userRepository->findByUsername($username);
        
        if (!$otherUser) {
            throw $this->createNotFoundException('User not found');
        }
        
        // Redirect to the conversation page
        return $this->redirectToRoute('app_message_conversation', [
            'username' => $username
        ]);
    }
    
    #[Route('/notifications', name: 'app_message_notifications', methods: ['GET'])]
    public function getNotifications(MessageRepository $messageRepository): JsonResponse
    {
        if (!$this->isGranted('ROLE_USER')) {
            return new JsonResponse(['count' => 0]);
        }
        
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $unreadCount = $messageRepository->findUnreadMessages($currentUser);
        
        return new JsonResponse([
            'count' => count($unreadCount)
        ]);
    }
} 
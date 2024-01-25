<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Comment;
use App\Entity\Ticket;
use App\Entity\User;
use App\Entity\Tag;
use App\Entity\Like;
use App\Form\TicketType;
use App\Form\CommentType;

#[Route("/ticket")]
class TicketController extends AbstractController
{
    #[Route('/', name: 'app_ticket')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $ticketRepository = $doctrine->getRepository(Ticket::class);
        return $this->render('ticket/tickets.html.twig', [
            'controller_name' => 'TicketController',
            'tickets'=>$ticketRepository->findAll(),
            'title'=>"Tous les tickets"
        ]);
    }
    #[Route('/{id<\d+>}', name: 'app_ticket_show')]
    public function show(int $id, ManagerRegistry $doctrine, Request $request): Response
    {
        $ticketRepository = $doctrine->getRepository(Ticket::class);
        $userRepository = $doctrine->getRepository(User::class); 
        $commentRepository = $doctrine->getRepository(Comment::class); 
        $likeRepository = $doctrine->getRepository(Like::class); 

        $user = $this->getUser();
        // Gestion des points d'activité
        $activity_point = $user->getActivityPoint();

        $ticket = $ticketRepository->find($id);
        $comments = $commentRepository->findBy(
            ['ticket' => $ticket],
            ['created_at' => 'ASC']
        );
        
        // Ajout de commentaire à la fin du ticket
        $form = null;
        if($user){
            $is_usefull = false;
            $comment = new Comment();
            $comment
                ->setAuthor($user)
                ->setTicket($ticket)
                ->setIsUsefull($is_usefull)
            ;
            $form = $this->createForm(CommentType::class, $comment);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $user->setActivityPoint($activity_point+1);
                $em = $doctrine->getManager();
                $em->persist($comment,$user);
                $em->flush();
                return $this->redirectToRoute('app_ticket_show',['id' => $id]);
            }
        }

        return $this->render('ticket/ticket.html.twig', [
            'controller_name' => 'TicketController',
            'ticket'=>$ticket,
            'title'=>"Le ticket",
            "form" => $form ? $form->createView() : null,
            "comments"=>$comments,
            "user" => $user
        ]);
    }
    #[Route('/ajouter', name: 'app_ticket_add')]
    public function add(ManagerRegistry $doctrine, Request $request): Response
    {
        // User connecté
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        $userRepository = $doctrine->getRepository(User::class);
        
        $ticket = new Ticket();
        $form = $this->createForm(TicketType::class, $ticket);
        $ticket->setAuthor($user);
        $form->handleRequest($request);

        // Gestion des points d'activité
        $activity_point = $user->getActivityPoint();

        if($form->isSubmitted() && $form->isValid()){
            $user->setActivityPoint($activity_point+1);
            $em = $doctrine->getManager();
			$em->persist($ticket, $user);
			$em->flush();

            

            return $this->redirectToRoute('app_ticket');
        }
        
        return $this->render('ticket/add.html.twig', [
            "form" => $form->createView(),
        ]);
    }
    #[Route('/{id<\d+>}/editer', name: 'app_ticket_edit')]
    public function edit(int $id, ManagerRegistry $doctrine, Request $request): Response
    {
        // Test si connecté et si l'utilisateur possède le ticket
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        $date = new \DateTime();
        $ticketRepository = $doctrine->getRepository(Ticket::class);
        $ticket = $ticketRepository->find($id);
        dump($ticket->getAuthor()->getId());

        if($user->getId() !== $ticket->getAuthor()->getId()){
            return $this->redirectToRoute('app_ticket');
        }

        $form = $this->createForm(TicketType::class, $ticket);
        $ticket->setModifiedAt($date);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em = $doctrine->getManager();
			$em->flush();
            return $this->redirectToRoute('app_ticket_show',[
                'id'=>$id,
                'title'=>"Modifier un ticket",
            ]);
        }
        return $this->render('ticket/add.html.twig', [
            "form" => $form->createView()
        ]);
    }
    #[Route('/{id<\d+>}/supprimer', name: 'app_ticket_delete')]
    public function delete(int $id, ManagerRegistry $doctrine, EntityManagerInterface $em): Response
    {
        $em = $doctrine->getManager();
        $ticketRepository = $doctrine->getRepository(Ticket::class);
        $ticket = $ticketRepository->find($id);
        $em->remove($ticket);
        $em->flush();
        return $this->redirectToRoute('app_ticket');
    }

    
    #[Route('/usefull', name: 'handle_usefull')]
    public function usefull(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);
        if($requestData){
            $commentid = $requestData['commentid'];

            $commentRepository = $doctrine->getRepository(Comment::class);
            $userRepository = $doctrine->getRepository(User::class);
            $ticketRepository = $doctrine->getRepository(Ticket::class);

            $comment= $commentRepository->find($commentid);
            $user= $userRepository->find($comment->getAuthor());
            $ticket= $ticketRepository->find($comment->getTicket());

            $activity_point = $user->getActivityPoint();

            $em = $doctrine->getManager();
            $comment->setIsUsefull(true);
            $user->setActivityPoint($activity_point+10);
            $em->persist($ticket, $user);
			$em->flush();

            // Exemple de réponse JSON
            $response = [
                'status' => 'success',
                'message' => 'Le commentaire a été utile !.',
            ];
            return new JsonResponse($response);
            }
        
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}


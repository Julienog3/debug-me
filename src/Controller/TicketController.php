<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Ticket;
use App\Entity\User;
use App\Entity\Tag;
use App\Form\TicketType;

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
    public function show(int $id, ManagerRegistry $doctrine): Response
    {
        $ticketRepository = $doctrine->getRepository(Ticket::class);
        return $this->render('ticket/ticket.html.twig', [
            'controller_name' => 'TicketController',
            'ticket'=>$ticketRepository->find($id),
            'title'=>"Le ticket"
        ]);
    }
    #[Route('/ajouter', name: 'app_ticket_add')]
    public function add(ManagerRegistry $doctrine, Request $request): Response
    {
        // User connecté
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        $author_id = $user->getId() ;
        $userRepository = $doctrine->getRepository(User::class);
        
        $ticket = new Ticket();
        $form = $this->createForm(TicketType::class, $ticket);
        $ticket->setAuthor($userRepository->find($author_id));
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){
            $em = $doctrine->getManager();
			$em->persist($ticket);
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
    

}

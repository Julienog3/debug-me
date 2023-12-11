<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Ticket;
use App\Form\TicketType;

#[Route("/ticket")]
class TicketController extends AbstractController
{
    #[Route('/', name: 'app_ticket')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $ticketRepository = $doctrine->getRepository(Ticket::class);
        dump($ticketRepository->findAll());
        return $this->render('ticket/ticket.html.twig', [
            'controller_name' => 'TicketController',
        ]);
    }
    #[Route('/{id<\d+>}', name: 'app_ticket_show')]
    public function show(int $id, ManagerRegistry $doctrine): Response
    {
        $ticketRepository = $doctrine->getRepository(Ticket::class);
        dump($ticketRepository->find($id));
        return $this->render('ticket/ticket.html.twig', [
            'controller_name' => 'TicketController',
        ]);
    }
    #[Route('/ajouter', name: 'app_ticket_add')]
    public function add(ManagerRegistry $doctrine, Request $request): Response
    {
        $ticket = new Ticket();
        $form = $this->createForm(TicketType::class, $ticket);
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
        $ticketRepository = $doctrine->getRepository(Ticket::class);
        $ticket = $ticketRepository->find($id);
        $form = $this->createForm(TicketType::class, $ticket);
        
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em = $doctrine->getManager();
			$em->flush();
            return $this->redirectToRoute('app_ticket_show',[
                'id'=>$id
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

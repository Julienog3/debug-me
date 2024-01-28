<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Comment;
use App\Entity\Ticket;
use App\Entity\User;
use App\Form\TicketType;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\TicketRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;

#[Route("/ticket")]
class TicketController extends AbstractController
{
    #[Route('/', name: 'app_ticket')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $ticketRepository = $doctrine->getRepository(Ticket::class);
        return $this->render('ticket/tickets.html.twig', [
            'tickets' => $ticketRepository->findAll(),
        ]);
    }
    #[Route('/{id<\d+>}', name: 'app_ticket_show')]
    public function show(int $id, ManagerRegistry $doctrine, Request $request, TicketRepository $ticketRepository): Response
    {
        $commentRepository = $doctrine->getRepository(Comment::class);

        $user = $this->getUser();
        $ticket = $ticketRepository->find($id);
        $comments = $commentRepository->findBy(
            ['ticket' => $ticket],
            ['created_at' => 'ASC']
        );

        // Ajout de commentaire à la fin du ticket
        $form = null;

        if ($user) {
            $comment = new Comment();
            $comment
                ->setAuthor($user)
                ->setTicket($ticket)
                ->setIsUsefull(false);

            $form = $this->createForm(CommentType::class, $comment);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $em = $doctrine->getManager();

                $user->addActivityPoint(1);

                $em->persist($comment);
                $em->flush();

                return $this->redirectToRoute('app_ticket_show', ['id' => $id]);
            }
        }

        return $this->render('ticket/ticket.html.twig', [
            'ticket' => $ticket,
            "form" => $form ? $form->createView() : null,
            "comments" => $comments,
            "user" => $user
        ]);
    }
    #[Route('/add', name: 'app_ticket_add')]
    public function add(ManagerRegistry $doctrine, Request $request): Response
    {
        // User connecté
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        $ticket = new Ticket();
        $form = $this->createForm(TicketType::class, $ticket);
        $ticket->setAuthor($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();

            $user->addActivityPoint(3);

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

        if ($user->getId() !== $ticket->getAuthor()->getId()) {
            return $this->redirectToRoute('app_ticket');
        }

        $form = $this->createForm(TicketType::class, $ticket);
        $ticket->setModifiedAt($date);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
            $em->flush();
            return $this->redirectToRoute('app_ticket_show', [
                'id' => $id,
            ]);
        }
        return $this->render('ticket/edit.html.twig', [
            "form" => $form->createView()
        ]);
    }
    #[Route('/{id<\d+>}/supprimer', name: 'app_ticket_delete')]
    public function delete(int $id, TicketRepository $ticketRepository, EntityManagerInterface $em): Response
    {
        $ticket = $ticketRepository->find($id);
        $em->remove($ticket);
        $em->flush();
        return $this->redirectToRoute('app_ticket');
    }

    #[Route('/{id<\d+>}/mark-as-done', name: 'app_ticket_done')]
    public function markTicketAsDone(int $id, TicketRepository $ticketRepository, EntityManagerInterface $em): RedirectResponse
    {
        $ticket = $ticketRepository->find($id);

        if (!$ticket) {
            throw $this->createNotFoundException('Ticket non trouvé');
        }

        $ticket->setDone(true);
        $em->flush();

        return $this->redirectToRoute('app_ticket_show', [
            'id' => $id,
        ]);
    }

    #[Route('/{ticketId<\d+>}/comment/{commentId<\d+>}/delete', name: 'app_ticket_comment_delete')]
    public function deleteComment(int $ticketId, int $commentId, CommentRepository $commentRepository, EntityManagerInterface $em)
    {
        $comment = $commentRepository->find($commentId);
        $em->remove($comment);
        $em->flush();

        return $this->redirectToRoute('app_ticket_show', [
            'id' => $ticketId,
        ]);
    }

    #[Route('/{ticketId<\d+>}/comment/{commentId<\d+>}/edit', name: 'app_ticket_comment_edit')]
    public function editComment(int $ticketId, int $commentId, Request $request, CommentRepository $commentRepository, EntityManagerInterface $em)
    {
        // Test si connecté et si l'utilisateur possède le ticket
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        $comment = $commentRepository->find($commentId);

        $form = $this->createForm(CommentType::class, $comment);
        $comment->setModifiedAt(new \DateTime());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('app_ticket_show', [
                'id' => $ticketId,
            ]);
        }
        return $this->render('comment/edit.html.twig', [
            "form" => $form->createView()
        ]);
    }
}

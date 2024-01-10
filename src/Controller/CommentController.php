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
use App\Form\CommentType;

#[Route("/comment")]
class CommentController extends AbstractController
{
    #[Route('/', name: 'app_comment')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $commentRepository = $doctrine->getRepository(Comment::class);
        dump($commentRepository->findAll());
        return $this->render('comment/comment.html.twig', [
            'controller_name' => 'CommentController',
        ]);
    }
    #[Route('/{id<\d+>}', name: 'app_comment_show')]
    public function show(int $id, ManagerRegistry $doctrine): Response
    {
        $commentRepository = $doctrine->getRepository(Comment::class);
        dump($commentRepository->find($id));
        return $this->render('comment/comment.html.twig', [
            'controller_name' => 'CommentController',
        ]);
    }
    #[Route('/ajouter', name: 'app_comment_add')]
    public function add(ManagerRegistry $doctrine, Request $request): Response
    {
        $author_id = 1 ; // Valeur à rendre dynamique en fonction de qui est connecté
        $ticket_id = 2 ; // Valeur à rendre dynamique en fonction de qui est connecté
        $is_usefull = false;

        $userRepository = $doctrine->getRepository(User::class);        
        $ticketRepository = $doctrine->getRepository(Ticket::class);

        $comment = new Comment();
        $comment
            ->setAuthor($userRepository->find($author_id))
            ->setTicket($ticketRepository->find($ticket_id))
            ->setIsUsefull($is_usefull)
        ;

        
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em = $doctrine->getManager();
			$em->persist($comment);
			$em->flush();
            return $this->redirectToRoute('app_comment');
        }
        return $this->render('comment/add.html.twig', [
            "form" => $form->createView(),
        ]);
    }
    #[Route('/{id<\d+>}/editer', name: 'app_comment_edit')]
    public function edit(int $id, ManagerRegistry $doctrine, Request $request): Response
    {
        $date = new \DateTime();
        $commentRepository = $doctrine->getRepository(Comment::class);
        $comment = $commentRepository->find($id);
        $comment->setModifiedAt($date);
        $form = $this->createForm(CommentType::class, $comment);
        
        
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em = $doctrine->getManager();
			$em->flush();
            return $this->redirectToRoute('app_comment_show',[
                'id'=>$id
            ]);
        }
        return $this->render('comment/add.html.twig', [
            "form" => $form->createView()
        ]);
    }
    #[Route('/{id<\d+>}/supprimer', name: 'app_comment_delete')]
    public function delete(int $id, ManagerRegistry $doctrine, EntityManagerInterface $em): Response
    {
        $em = $doctrine->getManager();
        $commentRepository = $doctrine->getRepository(Comment::class);
        $comment = $commentRepository->find($id);
        $em->remove($comment);
        $em->flush();
        return $this->redirectToRoute('app_comment');
    }
    

}

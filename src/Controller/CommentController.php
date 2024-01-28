<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Comment;
use App\Entity\Like;
use App\Entity\Ticket;
use App\Entity\User;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\LikeRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route("/comment")]
class CommentController extends AbstractController
{
    // #[Route('/{id<\d+>}/remove', name: 'app_comment_delete')]
    // public function deleteComment(int $ticketId, int $commentId, CommentRepository $commentRepository, EntityManagerInterface $em)
    // {
    //     $comment = $commentRepository->find($commentId);
    //     $em->remove($comment);
    //     $em->flush();
    // }

    #[Route('/{id<\d+>}/like', name: 'app_comment_like')]
    public function like(int $id, LikeRepository $likeRepository, CommentRepository $commentRepository, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        $comment = $commentRepository->find($id);

        if (!$comment) {
            throw $this->createNotFoundException('Commentaire non trouvé');
        }

        if ($comment->isLikedByUser($user)) {
            $like = $likeRepository->findOneBy(['comment' => $comment, 'user' => $user]);

            $em->remove($like);
            $em->flush();

            return $this->json(['code' => 200, 'likes' => $likeRepository->getCountForComment($comment)], 200);
        }

        $like = new Like();
        $like->setComment($comment)
            ->setUser($user);

        $em->persist($like);
        $em->flush();

        return $this->json(['code' => 200, 'likes' => $likeRepository->getCountForComment($comment)], 200);
    }
}

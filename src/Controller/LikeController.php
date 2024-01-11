<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Like;
use App\Entity\User;
use App\Entity\Comment;

#[Route("/like")]
class LikeController extends AbstractController
{
    #[Route('/', name: 'handle_like_ajax')]
    public function handleLikeAjax(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);
        $commentid = $requestData['commentid'];
        $userid = $requestData['userid'];
        $likeid = $requestData['likeid'];
        $action = $requestData['action'];

        $likeRepository = $doctrine->getRepository(Like::class); 
        $commentRepository = $doctrine->getRepository(Comment::class);
        $userRepository = $doctrine->getRepository(User::class); 
        
        $comment= $commentRepository->find($commentid);
        $user= $userRepository->find($userid);
        

        $em = $doctrine->getManager();
		
        if($action == "like"){
            $like = new Like();
            $like->setComment($comment);
            $like->setUser($user);
            $em->persist($like);
			
        }
        else{
            $like = $likeRepository->find($likeid);
            $em->remove($like);
        }
        $em->flush();

        
        // Exemple de réponse JSON
        $response = [
            'status' => 'success',
            'message' => 'Action de like ou unlike effectuée avec succès.',
        ];
        return new JsonResponse($response);
    }
}

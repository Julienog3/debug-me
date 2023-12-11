<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Form\EditUserType;

#[Route("/user")]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $userRepository = $doctrine->getRepository(User::class);
        dump($userRepository->findAll());
        return $this->render('user/user.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }
    #[Route('/{id<\d+>}', name: 'app_user_show')]
    public function show(int $id, ManagerRegistry $doctrine): Response
    {
        $userRepository = $doctrine->getRepository(User::class);
        dump($userRepository->find($id));
        return $this->render('user/user.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }
    #[Route('/{id<\d+>}/editer', name: 'app_user_edit')]
    public function edit(int $id, ManagerRegistry $doctrine, Request $request): Response
    {
        $userRepository = $doctrine->getRepository(User::class);
        $user = $userRepository->find($id);
        $form = $this->createForm(EditUserType::class, $user);
        
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em = $doctrine->getManager();
			$em->flush();
            return $this->redirectToRoute('app_user_show',[
                'id'=>$id
            ]);
        }
        return $this->render('user/edit.html.twig', [
            "form" => $form->createView()
        ]);
    }
    #[Route('/{id<\d+>}/supprimer', name: 'app_user_delete')]
    public function delete(int $id, ManagerRegistry $doctrine, EntityManagerInterface $em): Response
    {
        $em = $doctrine->getManager();
        $userRepository = $doctrine->getRepository(User::class);
        $user = $userRepository->find($id);
        $em->remove($user);
        $em->flush();
        return $this->redirectToRoute('app_user');
    }
    

}

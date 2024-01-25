<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Rank;
use App\Form\EditUserType;

#[Route("/user")]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $userRepository = $doctrine->getRepository(User::class);
        return $this->render('user/users.html.twig', [
            'controller_name' => 'UserController',
            'users'=>$userRepository->findAll(),
            'title'=>"Tous les utilisateurs"
        ]);
    }
    #[Route('/{id<\d+>}', name: 'app_user_show')]
    public function show(int $id, ManagerRegistry $doctrine): Response
    {
        $rankRepository = $doctrine->getRepository(Rank::class);
        $userRepository = $doctrine->getRepository(User::class);
        $user = $userRepository->find($id);
        $ranks = $rankRepository->findAll();
        
        // Déterminer le niveau de l'utilisateur
        $base_value = $user->getActivityPoint();
        $array_ranks = [];
        foreach ($ranks as $rank){
            $array_ranks[] = $rank->getRequiredPoint();
        }

        // Utilisez la fonction array_filter pour filtrer les valeurs supérieures à la valeur de base
        $filtered_array = array_filter($array_ranks, function ($value) use ($base_value) {
            return $value > $base_value;
        });
       
        // Si le tableau filtré n'est pas vide, trouvez la valeur minimale (la plus proche et supérieure)
        if (!empty($filtered_array)) {
            $closest_next_value = min($filtered_array); // Valeur du prochain rang
        }

        $filtered_array = array_filter($array_ranks, function ($value) use ($base_value) {
            return $value <= $base_value;
        });
        
        // Si le tableau filtré n'est pas vide, trouvez la valeur maximale (la plus proche et inférieure)
        if (!empty($filtered_array)) {
            $closest_last_value = max($filtered_array); // Valeur du rang précédent
        }
        $nextRank = $rankRepository->findBy(["required_point"=>$closest_next_value], [],1);
        $lastRank = $rankRepository->findBy(["required_point"=>$closest_last_value], [],1);

        return $this->render('user/user.html.twig', [
            'controller_name' => 'UserController',
            'user'=>$user,
            'title'=>"Un utilisateur",
            "nextRank"=>$nextRank[0],
            "lastRank"=>$lastRank[0]
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
            
            $uploadedFile = $form['icon']->getData();

            if ($uploadedFile) {
                $newFilename = uniqid().'.'.$uploadedFile->guessExtension();

                // Déplacez le fichier vers le répertoire où vous souhaitez le stocker
                $uploadedFile->move(
                    $this->getParameter('icon_directory'),
                    $newFilename
                );

                // Mettez à jour la propriété icon de l'entité User
                $user->setIcon($newFilename);
            }

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

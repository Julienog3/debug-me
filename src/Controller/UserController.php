<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Rank;
use App\Form\EditUserType;

#[Route("/profile")]
class UserController extends AbstractController
{
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
            return $value >= $base_value;
        });
       
        // Si le tableau filtré n'est pas vide, trouvez la valeur minimale (la plus proche et supérieure)
        if (!empty($filtered_array)) {
            $closest_value = min($filtered_array); // Valeur du prochain rang
        }


        return $this->render('user/user.html.twig', [
            'controller_name' => 'UserController',
            'user'=>$user,
            'title'=>"Un utilisateur",
            "nextRank"=>$closest_value
        ]);
    }

    #[Route('/', name: 'app_profile')]
    public function profile(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(EditUserType::class, $user);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()) {

            $iconFile = $form->get('icon')->getData();

            if ($iconFile) {
                $originalFilename = pathinfo($iconFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$iconFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $iconFile->move(
                        $this->getParameter('icons_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $user->setIcon($newFilename);
            }

			$entityManager->flush();
            return $this->redirectToRoute('app_home');
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    #[Route('/tickets', name: 'app_profile_tickets')]
    public function profileTickets(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $tickets = $user->getTickets();

        return $this->render('profile/tickets.html.twig', [
            'user' => $user,
            'tickets' => $tickets,
        ]);
    }
}

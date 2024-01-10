<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Rank;
use App\Form\RankType;

#[Route("/rank")]
class RankController extends AbstractController
{
    #[Route('/', name: 'app_rank')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $rankRepository = $doctrine->getRepository(Rank::class);
        return $this->render('rank/ranks.html.twig', [
            'controller_name' => 'rankController',
            'ranks' => $rankRepository->findAll(),
            'title' => "Les rangs"
        ]);
    }
    #[Route('/{id<\d+>}', name: 'app_rank_show')]
    public function show(int $id, ManagerRegistry $doctrine): Response
    {
        $rankRepository = $doctrine->getRepository(Rank::class);
        return $this->render('rank/rank.html.twig', [
            'controller_name' => 'rankController',
            'rank' => $rankRepository->find($id),
        ]);
    }
    #[Route('/ajouter', name: 'app_rank_add')]
    public function add(ManagerRegistry $doctrine, Request $request): Response
    {
        $rank = new Rank();
        $form = $this->createForm(RankType::class, $rank);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em = $doctrine->getManager();
			$em->persist($rank);
			$em->flush();
            return $this->redirectToRoute('app_rank');
        }
        return $this->render('rank/add.html.twig', [
            "form" => $form->createView(),
        ]);
    }
    #[Route('/{id<\d+>}/editer', name: 'app_rank_edit')]
    public function edit(int $id, ManagerRegistry $doctrine, Request $request): Response
    {
        $rankRepository = $doctrine->getRepository(Rank::class);
        $rank = $rankRepository->find($id);
        $form = $this->createForm(RankType::class, $rank);
        
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em = $doctrine->getManager();
			$em->flush();
            return $this->redirectToRoute('app_rank_show',[
                'id'=>$id
            ]);
        }
        return $this->render('rank/add.html.twig', [
            "form" => $form->createView()
        ]);
    }
    #[Route('/{id<\d+>}/supprimer', name: 'app_rank_delete')]
    public function delete(int $id, ManagerRegistry $doctrine, EntityManagerInterface $em): Response
    {
        $em = $doctrine->getManager();
        $rankRepository = $doctrine->getRepository(Rank::class);
        $rank = $rankRepository->find($id);
        $em->remove($rank);
        $em->flush();
        return $this->redirectToRoute('app_rank');
    }
    

}

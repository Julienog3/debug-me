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
use App\Repository\UserRepository;

#[Route("/rank")]
class RankController extends AbstractController
{
    #[Route('/', name: 'app_rank')]
    public function index(ManagerRegistry $doctrine, UserRepository $userRepository): Response
    {
        $users = $userRepository->findAllSortedByActivityPoints();

        return $this->render('rank/ranks.html.twig', [
            'users' => $users
        ]);
    }
}

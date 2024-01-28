<?php

namespace App\Controller;

use App\Repository\RankRepository;
use App\Repository\TicketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(RankRepository $rankRepository, TicketRepository $ticketRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        $currentRank = $rankRepository->findCurrentRank($user->getActivityPoint());
        $nextRank = $rankRepository->findNearestSuperiorRank($user->getActivityPoint());

        $tickets = $ticketRepository->findAll();

        return $this->render('home/index.html.twig', [
            'currentRank' => $currentRank,
            'nextRank' => $nextRank,
            'tickets' => $tickets
        ]);
    }
}

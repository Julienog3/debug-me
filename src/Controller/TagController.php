<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\tag;

#[Route("/tag")]
class TagController extends AbstractController
{
    #[Route('/', name: 'app_tag')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $tagRepository = $doctrine->getRepository(Tag::class);
        dump($tagRepository->findAll());
        return $this->render('tag/tag.html.twig', [
            'controller_name' => 'TagController',
        ]);
    }
}

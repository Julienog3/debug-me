<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Tag;
use App\Form\TagType;

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
    #[Route('/{id<\d+>}', name: 'app_tag_show')]
    public function show(int $id, ManagerRegistry $doctrine): Response
    {
        $tagRepository = $doctrine->getRepository(tag::class);
        dump($tagRepository->find($id));
        return $this->render('tag/tag.html.twig', [
            'controller_name' => 'TagController',
        ]);
    }
    #[Route('/ajouter', name: 'app_tag_add')]
    public function add(ManagerRegistry $doctrine, Request $request): Response
    {
        $tag = new Tag();
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em = $doctrine->getManager();
			$em->persist($tag);
			$em->flush();
            return $this->redirectToRoute('app_tag');
        }
        return $this->render('tag/add.html.twig', [
            "form" => $form->createView(),
        ]);
    }
    #[Route('/{id<\d+>}/editer', name: 'app_tag_edit')]
    public function edit(int $id, ManagerRegistry $doctrine, Request $request): Response
    {
        $tagRepository = $doctrine->getRepository(Tag::class);
        $tag = $tagRepository->find($id);
        $form = $this->createForm(TagType::class, $tag);
        
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em = $doctrine->getManager();
			$em->flush();
            return $this->redirectToRoute('app_tag_show',[
                'id'=>$id
            ]);
        }
        return $this->render('tag/add.html.twig', [
            "form" => $form->createView()
        ]);
    }
    #[Route('/{id<\d+>}/supprimer', name: 'app_tag_delete')]
    public function delete(int $id, ManagerRegistry $doctrine, EntityManagerInterface $em): Response
    {
        $em = $doctrine->getManager();
        $tagRepository = $doctrine->getRepository(Tag::class);
        $tag = $tagRepository->find($id);
        $em->remove($tag);
        $em->flush();
        return $this->redirectToRoute('app_tag');
    }
    

}

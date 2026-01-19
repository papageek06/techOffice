<?php

namespace App\Controller;

use App\Entity\Modele;
use App\Form\ModeleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/modele')]
final class ModeleController extends AbstractController
{
    #[Route(name: 'app_modele_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $modeles = $entityManager
            ->getRepository(Modele::class)
            ->findAll();

        return $this->render('modele/index.html.twig', [
            'modeles' => $modeles,
        ]);
    }

    #[Route('/new', name: 'app_modele_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $modele = new Modele();
        $form = $this->createForm(ModeleType::class, $modele);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($modele);
            $entityManager->flush();

            return $this->redirectToRoute('app_modele_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('modele/new.html.twig', [
            'modele' => $modele,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_modele_show', methods: ['GET'])]
    public function show(Modele $modele): Response
    {
        return $this->render('modele/show.html.twig', [
            'modele' => $modele,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_modele_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Modele $modele, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ModeleType::class, $modele);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_modele_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('modele/edit.html.twig', [
            'modele' => $modele,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_modele_delete', methods: ['POST'])]
    public function delete(Request $request, Modele $modele, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$modele->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($modele);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_modele_index', [], Response::HTTP_SEE_OTHER);
    }
}

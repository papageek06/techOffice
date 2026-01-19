<?php

namespace App\Controller;

use App\Entity\ReleveCompteur;
use App\Form\ReleveCompteurType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/releve/compteur')]
final class ReleveCompteurController extends AbstractController
{
    #[Route(name: 'app_releve_compteur_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $releveCompteurs = $entityManager
            ->getRepository(ReleveCompteur::class)
            ->findAll();

        return $this->render('releve_compteur/index.html.twig', [
            'releve_compteurs' => $releveCompteurs,
        ]);
    }

    #[Route('/new', name: 'app_releve_compteur_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $releveCompteur = new ReleveCompteur();
        $form = $this->createForm(ReleveCompteurType::class, $releveCompteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($releveCompteur);
            $entityManager->flush();

            return $this->redirectToRoute('app_releve_compteur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('releve_compteur/new.html.twig', [
            'releve_compteur' => $releveCompteur,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_releve_compteur_show', methods: ['GET'])]
    public function show(ReleveCompteur $releveCompteur): Response
    {
        return $this->render('releve_compteur/show.html.twig', [
            'releve_compteur' => $releveCompteur,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_releve_compteur_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ReleveCompteur $releveCompteur, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReleveCompteurType::class, $releveCompteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_releve_compteur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('releve_compteur/edit.html.twig', [
            'releve_compteur' => $releveCompteur,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_releve_compteur_delete', methods: ['POST'])]
    public function delete(Request $request, ReleveCompteur $releveCompteur, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$releveCompteur->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($releveCompteur);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_releve_compteur_index', [], Response::HTTP_SEE_OTHER);
    }
}

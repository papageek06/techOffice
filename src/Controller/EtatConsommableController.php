<?php

namespace App\Controller;

use App\Entity\EtatConsommable;
use App\Form\EtatConsommableType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/etat/consommable')]
final class EtatConsommableController extends AbstractController
{
    #[Route(name: 'app_etat_consommable_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $etatConsommables = $entityManager
            ->getRepository(EtatConsommable::class)
            ->findAll();

        return $this->render('etat_consommable/index.html.twig', [
            'etat_consommables' => $etatConsommables,
        ]);
    }

    #[Route('/new', name: 'app_etat_consommable_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $etatConsommable = new EtatConsommable();
        $form = $this->createForm(EtatConsommableType::class, $etatConsommable);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($etatConsommable);
            $entityManager->flush();

            return $this->redirectToRoute('app_etat_consommable_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('etat_consommable/new.html.twig', [
            'etat_consommable' => $etatConsommable,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_etat_consommable_show', methods: ['GET'])]
    public function show(EtatConsommable $etatConsommable): Response
    {
        return $this->render('etat_consommable/show.html.twig', [
            'etat_consommable' => $etatConsommable,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_etat_consommable_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EtatConsommable $etatConsommable, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EtatConsommableType::class, $etatConsommable);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_etat_consommable_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('etat_consommable/edit.html.twig', [
            'etat_consommable' => $etatConsommable,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_etat_consommable_delete', methods: ['POST'])]
    public function delete(Request $request, EtatConsommable $etatConsommable, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$etatConsommable->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($etatConsommable);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_etat_consommable_index', [], Response::HTTP_SEE_OTHER);
    }
}

<?php

namespace App\Controller;

use App\Entity\Imprimante;
use App\Form\ImprimanteType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/imprimante')]
final class ImprimanteController extends AbstractController
{
    #[Route(name: 'app_imprimante_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $imprimantes = $entityManager
            ->getRepository(Imprimante::class)
            ->findAll();

        return $this->render('imprimante/index.html.twig', [
            'imprimantes' => $imprimantes,
        ]);
    }

    #[Route('/new', name: 'app_imprimante_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $imprimante = new Imprimante();
        $form = $this->createForm(ImprimanteType::class, $imprimante);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($imprimante);
            $entityManager->flush();

            return $this->redirectToRoute('app_imprimante_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('imprimante/new.html.twig', [
            'imprimante' => $imprimante,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_imprimante_show', methods: ['GET'])]
    public function show(Imprimante $imprimante): Response
    {
        return $this->render('imprimante/show.html.twig', [
            'imprimante' => $imprimante,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_imprimante_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Imprimante $imprimante, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ImprimanteType::class, $imprimante);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_imprimante_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('imprimante/edit.html.twig', [
            'imprimante' => $imprimante,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_imprimante_delete', methods: ['POST'])]
    public function delete(Request $request, Imprimante $imprimante, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$imprimante->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($imprimante);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_imprimante_index', [], Response::HTTP_SEE_OTHER);
    }
}

<?php

namespace App\Controller;

use App\Entity\PieceModele;
use App\Form\PieceModeleType;
use App\Repository\PieceModeleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/piece-modele')]
final class PieceModeleController extends AbstractController
{
    #[Route(name: 'app_piece_modele_index', methods: ['GET'])]
    public function index(PieceModeleRepository $pieceModeleRepository): Response
    {
        return $this->render('piece_modele/index.html.twig', [
            'piece_modeles' => $pieceModeleRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_piece_modele_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $pieceModele = new PieceModele();
        $form = $this->createForm(PieceModeleType::class, $pieceModele);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($pieceModele);
            $entityManager->flush();

            return $this->redirectToRoute('app_piece_modele_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('piece_modele/new.html.twig', [
            'piece_modele' => $pieceModele,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_piece_modele_show', methods: ['GET'])]
    public function show(PieceModele $pieceModele): Response
    {
        return $this->render('piece_modele/show.html.twig', [
            'piece_modele' => $pieceModele,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_piece_modele_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PieceModele $pieceModele, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PieceModeleType::class, $pieceModele);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_piece_modele_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('piece_modele/edit.html.twig', [
            'piece_modele' => $pieceModele,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_piece_modele_delete', methods: ['POST'])]
    public function delete(Request $request, PieceModele $pieceModele, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$pieceModele->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($pieceModele);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_piece_modele_index', [], Response::HTTP_SEE_OTHER);
    }
}

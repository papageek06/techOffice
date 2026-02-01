<?php

namespace App\Controller;

use App\Entity\Piece;
use App\Form\PieceType;
use App\Repository\PieceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/piece')]
final class PieceController extends AbstractController
{
    #[Route(name: 'app_piece_index', methods: ['GET'])]
    public function index(PieceRepository $pieceRepository): Response
    {
        // Charger les pièces avec leurs modèles compatibles et leurs stocks pour éviter les requêtes N+1
        $pieces = $pieceRepository->createQueryBuilder('p')
            ->leftJoin('p.pieceModeles', 'pm')
            ->addSelect('pm')
            ->leftJoin('pm.modele', 'm')
            ->addSelect('m')
            ->leftJoin('m.fabricant', 'f')
            ->addSelect('f')
            ->leftJoin('p.stockItems', 'si')
            ->addSelect('si')
            ->leftJoin('si.stockLocation', 'sl')
            ->addSelect('sl')
            ->orderBy('p.reference', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('piece/index.html.twig', [
            'pieces' => $pieces,
        ]);
    }

    #[Route('/new', name: 'app_piece_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $piece = new Piece();
        $form = $this->createForm(PieceType::class, $piece);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($piece);
            $entityManager->flush();

            return $this->redirectToRoute('app_piece_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('piece/new.html.twig', [
            'piece' => $piece,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_piece_show', methods: ['GET'])]
    public function show(Piece $piece, EntityManagerInterface $entityManager): Response
    {
        // Charger la pièce avec ses relations pour éviter les requêtes N+1
        $piece = $entityManager
            ->getRepository(Piece::class)
            ->createQueryBuilder('p')
            ->leftJoin('p.pieceModeles', 'pm')
            ->addSelect('pm')
            ->leftJoin('pm.modele', 'm')
            ->addSelect('m')
            ->leftJoin('m.fabricant', 'f')
            ->addSelect('f')
            ->where('p.id = :id')
            ->setParameter('id', $piece->getId())
            ->getQuery()
            ->getOneOrNullResult();

        // Charger tous les modèles pour le sélecteur
        $modeles = $entityManager
            ->getRepository(\App\Entity\Modele::class)
            ->createQueryBuilder('m')
            ->leftJoin('m.fabricant', 'f')
            ->addSelect('f')
            ->orderBy('f.nomFabricant', 'ASC')
            ->addOrderBy('m.referenceModele', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('piece/show.html.twig', [
            'piece' => $piece,
            'modeles' => $modeles,
        ]);
    }

    #[Route('/{id}/add-modele', name: 'app_piece_add_modele', methods: ['POST'])]
    public function addModele(Request $request, Piece $piece, EntityManagerInterface $entityManager): Response
    {
        // Cette route accepte les requêtes AJAX pour ajouter une correspondance pièce/modèle

        $modeleId = $request->request->getInt('modele_id');
        $roleValue = $request->request->get('role');

        if (!$modeleId || !$roleValue) {
            return $this->json(['error' => 'Paramètres manquants'], Response::HTTP_BAD_REQUEST);
        }

        $modele = $entityManager->getRepository(\App\Entity\Modele::class)->find($modeleId);
        if (!$modele) {
            return $this->json(['error' => 'Modèle introuvable'], Response::HTTP_NOT_FOUND);
        }

        try {
            $role = \App\Enum\PieceRoleModele::from($roleValue);
        } catch (\ValueError $e) {
            return $this->json(['error' => 'Rôle invalide'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier si la correspondance existe déjà
        $existing = $entityManager
            ->getRepository(\App\Entity\PieceModele::class)
            ->findOneBy([
                'piece' => $piece,
                'modele' => $modele,
                'role' => $role,
            ]);

        if ($existing) {
            return $this->json(['error' => 'Cette correspondance existe déjà'], Response::HTTP_CONFLICT);
        }

        // Créer la nouvelle correspondance
        $pieceModele = new \App\Entity\PieceModele();
        $pieceModele->setPiece($piece);
        $pieceModele->setModele($modele);
        $pieceModele->setRole($role);

        $entityManager->persist($pieceModele);
        $entityManager->flush();

        // Retourner les données de la nouvelle correspondance
        return $this->json([
            'success' => true,
            'pieceModele' => [
                'id' => $pieceModele->getId(),
                'modele' => [
                    'id' => $modele->getId(),
                    'fabricant' => $modele->getFabricant()->getNomFabricant(),
                    'referenceModele' => $modele->getReferenceModele(),
                ],
                'role' => $role->value,
            ],
        ]);
    }

    #[Route('/{id}/edit', name: 'app_piece_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Piece $piece, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PieceType::class, $piece);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_piece_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('piece/edit.html.twig', [
            'piece' => $piece,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_piece_delete', methods: ['POST'])]
    public function delete(Request $request, Piece $piece, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$piece->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($piece);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_piece_index', [], Response::HTTP_SEE_OTHER);
    }
}

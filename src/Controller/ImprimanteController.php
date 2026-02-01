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
        // Charger les imprimantes avec leurs relations pour éviter les requêtes N+1
        $imprimantes = $entityManager
            ->getRepository(Imprimante::class)
            ->createQueryBuilder('i')
            ->leftJoin('i.modele', 'm')
            ->addSelect('m')
            ->leftJoin('m.fabricant', 'f')
            ->addSelect('f')
            ->leftJoin('i.site', 's')
            ->addSelect('s')
            ->orderBy('i.numeroSerie', 'ASC')
            ->getQuery()
            ->getResult();

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
    public function show(Imprimante $imprimante, EntityManagerInterface $entityManager): Response
    {
        // Récupérer tous les relevés triés par date de scan (LAST_SCAN_DATE) - plus récent en premier
        $releves = $entityManager
            ->getRepository(\App\Entity\ReleveCompteur::class)
            ->createQueryBuilder('r')
            ->where('r.imprimante = :imprimante')
            ->setParameter('imprimante', $imprimante)
            ->orderBy('r.dateReleve', 'DESC')
            ->getQuery()
            ->getResult();

        // Récupérer tous les états consommables triés par date de scan (LAST_SCAN_DATE) - plus récent en premier
        $etats = $entityManager
            ->getRepository(\App\Entity\EtatConsommable::class)
            ->createQueryBuilder('e')
            ->where('e.imprimante = :imprimante')
            ->setParameter('imprimante', $imprimante)
            ->orderBy('e.dateCapture', 'DESC')
            ->getQuery()
            ->getResult();

        // Récupérer toutes les pièces compatibles avec le modèle de l'imprimante
        $pieceModeles = $entityManager
            ->getRepository(\App\Entity\PieceModele::class)
            ->findPiecesForModele($imprimante->getModele());

        // Récupérer le stock CLIENT du site de l'imprimante (si existe)
        $stockLocationRepository = $entityManager->getRepository(\App\Entity\StockLocation::class);
        $stockClient = $stockLocationRepository->findClientStockForSite($imprimante->getSite());

        // Créer un tableau associatif pour faciliter l'accès au stock par pièce
        $stockParPiece = [];
        if ($stockClient) {
            foreach ($stockClient->getStockItems() as $stockItem) {
                $stockParPiece[$stockItem->getPiece()->getId()] = $stockItem;
            }
        }

        return $this->render('imprimante/show.html.twig', [
            'imprimante' => $imprimante,
            'releves' => $releves,
            'etats' => $etats,
            'pieceModeles' => $pieceModeles,
            'stockClient' => $stockClient,
            'stockParPiece' => $stockParPiece,
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

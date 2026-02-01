<?php

namespace App\Controller;

use App\Entity\Site;
use App\Form\SiteType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/site')]
final class SiteController extends AbstractController
{
    #[Route(name: 'app_site_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $search = $request->query->get('search', '');
        $isAjax = $request->isXmlHttpRequest() || $request->query->getBoolean('ajax', false);
        
        // Charger les sites avec leurs imprimantes, modèles, fabricants, relevés et états consommables
        $qb = $entityManager
            ->getRepository(Site::class)
            ->createQueryBuilder('s')
            ->leftJoin('s.imprimantes', 'i')
            ->leftJoin('i.modele', 'm')
            ->leftJoin('m.fabricant', 'f')
            ->leftJoin('i.relevesCompteur', 'r')
            ->leftJoin('i.etatsConsommable', 'e')
            ->addSelect('i')
            ->addSelect('m')
            ->addSelect('f')
            ->addSelect('r')
            ->addSelect('e');

        // Filtrer par nom de site ou numéro de série d'imprimante
        if (!empty($search)) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('LOWER(s.nomSite)', 'LOWER(:search)'),
                    $qb->expr()->like('LOWER(i.numeroSerie)', 'LOWER(:search)')
                )
            )
            ->setParameter('search', '%' . $search . '%');
        }

        $sites = $qb
            ->orderBy('s.nomSite', 'ASC')
            ->addOrderBy('i.numeroSerie', 'ASC')
            ->getQuery()
            ->getResult();
        
        // Supprimer les doublons de sites (peuvent apparaître si plusieurs imprimantes correspondent)
        $uniqueSites = [];
        foreach ($sites as $site) {
            if (!isset($uniqueSites[$site->getId()])) {
                $uniqueSites[$site->getId()] = $site;
            }
        }
        $sites = array_values($uniqueSites);

        // Si requête AJAX, retourner uniquement le contenu des résultats
        if ($isAjax) {
            return $this->render('site/_results.html.twig', [
                'sites' => $sites,
                'search' => $search,
            ]);
        }

        return $this->render('site/index.html.twig', [
            'sites' => $sites,
            'search' => $search,
        ]);
    }

    #[Route('/new', name: 'app_site_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $site = new Site();
        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($site);
            $entityManager->flush();

            return $this->redirectToRoute('app_site_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('site/new.html.twig', [
            'site' => $site,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_site_show', methods: ['GET'])]
    public function show(Site $site, EntityManagerInterface $entityManager): Response
    {
        // Charger toutes les données nécessaires avec eager loading pour éviter N+1
        $site = $entityManager->getRepository(Site::class)
            ->createQueryBuilder('s')
            ->leftJoin('s.imprimantes', 'i')
            ->leftJoin('i.modele', 'm')
            ->leftJoin('m.fabricant', 'f')
            ->leftJoin('i.relevesCompteur', 'r')
            ->leftJoin('i.etatsConsommable', 'e')
            ->leftJoin('s.stockLocations', 'sl')
            ->leftJoin('sl.stockItems', 'si')
            ->leftJoin('si.piece', 'p')
            ->addSelect('i')
            ->addSelect('m')
            ->addSelect('f')
            ->addSelect('r')
            ->addSelect('e')
            ->addSelect('sl')
            ->addSelect('si')
            ->addSelect('p')
            ->where('s.id = :id')
            ->setParameter('id', $site->getId())
            ->getQuery()
            ->getOneOrNullResult();

        if (!$site) {
            throw $this->createNotFoundException('Site non trouvé');
        }

        // Charger les correspondances pièce/modèle pour tous les modèles d'imprimantes du site
        $modeleIds = [];
        foreach ($site->getImprimantes() as $imprimante) {
            $modeleIds[] = $imprimante->getModele()->getId();
        }

        $pieceModeles = [];
        $stockParPiece = [];
        $pieceModelesParModele = [];
        
        if (!empty($modeleIds)) {
            // Charger toutes les correspondances pièce/modèle pour les modèles du site
            $pieceModeles = $entityManager
                ->getRepository(\App\Entity\PieceModele::class)
                ->createQueryBuilder('pm')
                ->leftJoin('pm.piece', 'piece')
                ->leftJoin('pm.modele', 'modele')
                ->addSelect('piece')
                ->addSelect('modele')
                ->where('pm.modele IN (:modeleIds)')
                ->setParameter('modeleIds', $modeleIds)
                ->getQuery()
                ->getResult();

            // Créer un index par modèle pour faciliter l'accès
            foreach ($pieceModeles as $pm) {
                $modeleId = $pm->getModele()->getId();
                if (!isset($pieceModelesParModele[$modeleId])) {
                    $pieceModelesParModele[$modeleId] = [];
                }
                $pieceModelesParModele[$modeleId][] = $pm;
            }
            
            // Trier les pièces par modèle (toners en premier)
            foreach ($pieceModelesParModele as $modeleId => &$pieces) {
                usort($pieces, function($a, $b) {
                    $roleA = $a->getRole()->value;
                    $roleB = $b->getRole()->value;
                    
                    // Les toners en premier
                    $isTonerA = str_starts_with($roleA, 'TONER_');
                    $isTonerB = str_starts_with($roleB, 'TONER_');
                    
                    if ($isTonerA && !$isTonerB) {
                        return -1;
                    }
                    if (!$isTonerA && $isTonerB) {
                        return 1;
                    }
                    
                    // Si les deux sont des toners ou non-toners, trier par ordre alphabétique
                    return strcmp($roleA, $roleB);
                });
            }
            unset($pieces); // Libérer la référence
        }

        // Créer un index des stocks par pièce pour tous les stocks du site
        foreach ($site->getStockLocations() as $stockLocation) {
            foreach ($stockLocation->getStockItems() as $stockItem) {
                $pieceId = $stockItem->getPiece()->getId();
                if (!isset($stockParPiece[$pieceId])) {
                    $stockParPiece[$pieceId] = [
                        'quantite' => 0,
                        'seuilAlerte' => null,
                        'stockLocation' => $stockLocation,
                    ];
                }
                $stockParPiece[$pieceId]['quantite'] += $stockItem->getQuantite();
                // Prendre le seuil d'alerte du premier stock trouvé (ou le plus bas)
                if ($stockItem->getSeuilAlerte() !== null) {
                    if ($stockParPiece[$pieceId]['seuilAlerte'] === null || 
                        $stockItem->getSeuilAlerte() < $stockParPiece[$pieceId]['seuilAlerte']) {
                        $stockParPiece[$pieceId]['seuilAlerte'] = $stockItem->getSeuilAlerte();
                    }
                }
            }
        }

        return $this->render('site/show.html.twig', [
            'site' => $site,
            'pieceModelesParModele' => $pieceModelesParModele,
            'stockParPiece' => $stockParPiece,
        ]);
    }

    #[Route('/{id}/alertes', name: 'app_site_alertes', methods: ['GET'])]
    public function getAlertes(Site $site, EntityManagerInterface $entityManager): Response
    {
        // Endpoint AJAX pour récupérer le nombre d'alertes
        // TODO: Implémenter la logique avec TonerAlertService
        $alertesCount = 0; // Placeholder
        
        return $this->json([
            'count' => $alertesCount,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_site_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Site $site, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_site_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('site/edit.html.twig', [
            'site' => $site,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_site_delete', methods: ['POST'])]
    public function delete(Request $request, Site $site, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$site->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($site);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_site_index', [], Response::HTTP_SEE_OTHER);
    }
}

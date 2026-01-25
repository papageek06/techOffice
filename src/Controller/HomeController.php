<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Intervention;
use App\Entity\Site;
use App\Enum\StatutIntervention;
use App\Enum\StatutImprimante;
use App\Enum\StockLocationType;
use App\Repository\ClientRepository;
use App\Repository\ImprimanteRepository;
use App\Repository\InterventionRepository;
use App\Repository\SiteRepository;
use App\Repository\StockLocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        EntityManagerInterface $entityManager,
        ClientRepository $clientRepository,
        SiteRepository $siteRepository,
        ImprimanteRepository $imprimanteRepository,
        InterventionRepository $interventionRepository,
        StockLocationRepository $stockLocationRepository
    ): Response {
        // Statistiques globales
        $stats = [
            'clients' => count($clientRepository->findAll()),
            'sites' => count($siteRepository->findAll()),
            'imprimantes' => count($imprimanteRepository->findAll()),
            'imprimantes_suivies' => count($imprimanteRepository->findBy(['suivieParService' => true])),
            'interventions_ouvertes' => count($interventionRepository->findBy(['statut' => StatutIntervention::OUVERTE])),
            'stocks' => count($stockLocationRepository->findAll()),
        ];

        // Dernières interventions
        $dernieresInterventions = $interventionRepository->createQueryBuilder('i')
            ->orderBy('i.dateCreation', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        // Sites avec alertes (stocks bas)
        $sitesAvecAlertes = [];
        try {
            $sitesAvecAlertes = $siteRepository->createQueryBuilder('s')
                ->join('s.stockLocations', 'sl')
                ->join('sl.stockItems', 'si')
                ->where('sl.type = :typeClient')
                ->andWhere('si.seuilAlerte IS NOT NULL')
                ->andWhere('si.quantite <= si.seuilAlerte')
                ->setParameter('typeClient', StockLocationType::CLIENT)
                ->groupBy('s.id')
                ->getQuery()
                ->getResult();
        } catch (\Exception $e) {
            // Ignorer si les tables n'existent pas encore
        }

        // Imprimantes nécessitant attention (niveau d'encre bas)
        $imprimantesAttention = $imprimanteRepository->createQueryBuilder('i')
            ->where('i.suivieParService = true')
            ->andWhere('i.statut = :statut')
            ->setParameter('statut', StatutImprimante::ACTIF)
            ->getQuery()
            ->getResult();

        return $this->render('home/index.html.twig', [
            'stats' => $stats,
            'dernieresInterventions' => $dernieresInterventions,
            'sitesAvecAlertes' => $sitesAvecAlertes,
            'imprimantesAttention' => $imprimantesAttention,
        ]);
    }
}

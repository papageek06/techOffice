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
        // Statistiques globales (utilisation de COUNT pour éviter de charger toutes les entités)
        $countClients = $clientRepository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
        
        $countSites = $siteRepository->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->getQuery()
            ->getSingleScalarResult();
        
        $countImprimantes = $imprimanteRepository->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->getQuery()
            ->getSingleScalarResult();
        
        $countImprimantesSuivies = $imprimanteRepository->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->where('i.suivieParService = :suivie')
            ->setParameter('suivie', true)
            ->getQuery()
            ->getSingleScalarResult();
        
        $countInterventionsOuvertes = $interventionRepository->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->where('i.statut = :statut')
            ->setParameter('statut', StatutIntervention::OUVERTE)
            ->getQuery()
            ->getSingleScalarResult();
        
        $countStocks = $stockLocationRepository->createQueryBuilder('sl')
            ->select('COUNT(sl.id)')
            ->getQuery()
            ->getSingleScalarResult();
        
        $stats = [
            'clients' => (int) ($countClients ?? 0),
            'sites' => (int) ($countSites ?? 0),
            'imprimantes' => (int) ($countImprimantes ?? 0),
            'imprimantes_suivies' => (int) ($countImprimantesSuivies ?? 0),
            'interventions_ouvertes' => (int) ($countInterventionsOuvertes ?? 0),
            'stocks' => (int) ($countStocks ?? 0),
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

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
        // Vérifier si l'utilisateur est connecté
        $user = $this->getUser();
        
        // Si l'utilisateur n'est pas connecté, on ne charge pas les données
        if (!$user) {
            return $this->render('home/index.html.twig', [
                'is_authenticated' => false,
                'stats' => [],
                'dernieresInterventions' => [],
                'sitesAvecAlertes' => [],
                'imprimantesAttention' => [],
            ]);
        }
        
        // Statistiques globales (utilisation de COUNT pour éviter de charger toutes les entités)
        // Gestion des erreurs de connexion avec try-catch
        $countClients = 0;
        try {
            $countClients = (int) $clientRepository->createQueryBuilder('c')
                ->select('COUNT(c.id)')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Exception $e) {
            // En cas d'erreur de connexion, on garde 0
        }
        
        $countSites = 0;
        try {
            $countSites = (int) $siteRepository->createQueryBuilder('s')
                ->select('COUNT(s.id)')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Exception $e) {
            // En cas d'erreur de connexion, on garde 0
        }
        
        $countImprimantes = 0;
        try {
            $countImprimantes = (int) $imprimanteRepository->createQueryBuilder('i')
                ->select('COUNT(i.id)')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Exception $e) {
            // En cas d'erreur de connexion, on garde 0
        }
        
        $countImprimantesSuivies = 0;
        try {
            $countImprimantesSuivies = (int) $imprimanteRepository->createQueryBuilder('i')
                ->select('COUNT(i.id)')
                ->where('i.suivieParService = :suivie')
                ->setParameter('suivie', true)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Exception $e) {
            // En cas d'erreur de connexion, on garde 0
        }
        
        $countInterventionsOuvertes = 0;
        try {
            $countInterventionsOuvertes = (int) $interventionRepository->createQueryBuilder('i')
                ->select('COUNT(i.id)')
                ->where('i.statut = :statut')
                ->setParameter('statut', StatutIntervention::OUVERTE)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Exception $e) {
            // En cas d'erreur de connexion, on garde 0
        }
        
        $countStocks = 0;
        try {
            $countStocks = (int) $stockLocationRepository->createQueryBuilder('sl')
                ->select('COUNT(sl.id)')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Exception $e) {
            // En cas d'erreur de connexion, on garde 0
        }
        
        $stats = [
            'clients' => $countClients,
            'sites' => $countSites,
            'imprimantes' => $countImprimantes,
            'imprimantes_suivies' => $countImprimantesSuivies,
            'interventions_ouvertes' => $countInterventionsOuvertes,
            'stocks' => $countStocks,
        ];

        // Dernières interventions
        $dernieresInterventions = [];
        try {
            $dernieresInterventions = $interventionRepository->createQueryBuilder('i')
                ->orderBy('i.dateCreation', 'DESC')
                ->setMaxResults(5)
                ->getQuery()
                ->getResult();
        } catch (\Exception $e) {
            // En cas d'erreur de connexion, on garde un tableau vide
        }

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
            // Ignorer si les tables n'existent pas encore ou en cas d'erreur de connexion
        }

        // Imprimantes nécessitant attention (niveau d'encre bas)
        $imprimantesAttention = [];
        try {
            $imprimantesAttention = $imprimanteRepository->createQueryBuilder('i')
                ->where('i.suivieParService = true')
                ->andWhere('i.statut = :statut')
                ->setParameter('statut', StatutImprimante::ACTIF)
                ->getQuery()
                ->getResult();
        } catch (\Exception $e) {
            // En cas d'erreur de connexion, on garde un tableau vide
        }

        return $this->render('home/index.html.twig', [
            'is_authenticated' => true,
            'stats' => $stats,
            'dernieresInterventions' => $dernieresInterventions,
            'sitesAvecAlertes' => $sitesAvecAlertes,
            'imprimantesAttention' => $imprimantesAttention,
        ]);
    }
}

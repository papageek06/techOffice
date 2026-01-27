<?php

namespace App\Controller;

use App\Entity\Contrat;
use App\Entity\FacturationPeriode;
use App\Enum\StatutFacturation;
use App\Repository\ContratRepository;
use App\Repository\FacturationPeriodeRepository;
use App\Service\BillingCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;

/**
 * Contrôleur pour la section Comptabilité
 * Accessible uniquement aux rôles ROLE_COMPTABLE et ROLE_ADMIN
 */
#[Route('/comptabilite')]
final class ComptabiliteController extends AbstractController
{
    #[Route('', name: 'app_comptabilite_dashboard', methods: ['GET'])]
    #[IsGranted(new Expression("is_granted('ROLE_COMPTABLE') or is_granted('ROLE_ADMIN')"))]
    public function dashboard(
        EntityManagerInterface $entityManager,
        ContratRepository $contratRepository,
        FacturationPeriodeRepository $facturationPeriodeRepository,
        BillingCalculator $billingCalculator
    ): Response {
        // Statistiques globales
        $stats = [
            'contrats_actifs' => count($contratRepository->findActifs()),
            'periodes_brouillon' => count($facturationPeriodeRepository->findBy(['statut' => StatutFacturation::BROUILLON])),
            'periodes_validees' => count($facturationPeriodeRepository->findBy(['statut' => StatutFacturation::VALIDE])),
            'periodes_facturees' => count($facturationPeriodeRepository->findBy(['statut' => StatutFacturation::FACTURE])),
        ];

        // Périodes en brouillon (à valider)
        $periodesBrouillon = $facturationPeriodeRepository->createQueryBuilder('fp')
            ->join('fp.contratLigne', 'cl')
            ->join('cl.contrat', 'c')
            ->join('cl.site', 's')
            ->where('fp.statut = :statut')
            ->setParameter('statut', StatutFacturation::BROUILLON)
            ->orderBy('fp.dateDebut', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        // Calculer les montants pour chaque période en brouillon
        $periodesAvecMontants = [];
        foreach ($periodesBrouillon as $periode) {
            $calcul = $billingCalculator->calculateForPeriod($periode);
            $periodesAvecMontants[] = [
                'periode' => $periode,
                'montant' => $calcul['montant'],
                'pagesNoir' => $calcul['pagesNoir'],
                'pagesCouleur' => $calcul['pagesCouleur'],
            ];
        }

        // Périodes validées récentes
        $periodesValidees = $facturationPeriodeRepository->createQueryBuilder('fp')
            ->join('fp.contratLigne', 'cl')
            ->join('cl.contrat', 'c')
            ->join('cl.site', 's')
            ->where('fp.statut = :statut')
            ->setParameter('statut', StatutFacturation::VALIDE)
            ->orderBy('fp.createdAt', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        // Montant total des périodes validées
        $montantTotalValide = 0;
        foreach ($periodesValidees as $periode) {
            $calcul = $billingCalculator->calculateForPeriod($periode);
            $montantTotalValide += $calcul['montant'];
        }

        return $this->render('comptabilite/dashboard.html.twig', [
            'stats' => $stats,
            'periodesAvecMontants' => $periodesAvecMontants,
            'periodesValidees' => $periodesValidees,
            'montantTotalValide' => round($montantTotalValide, 2),
        ]);
    }

    #[Route('/contrats', name: 'app_comptabilite_contrats', methods: ['GET'])]
    #[IsGranted(new Expression("is_granted('ROLE_COMPTABLE') or is_granted('ROLE_ADMIN')"))]
    public function contrats(ContratRepository $contratRepository): Response
    {
        $contrats = $contratRepository->findAll();

        return $this->render('comptabilite/contrats.html.twig', [
            'contrats' => $contrats,
        ]);
    }

    #[Route('/periodes', name: 'app_comptabilite_periodes', methods: ['GET'])]
    #[IsGranted(new Expression("is_granted('ROLE_COMPTABLE') or is_granted('ROLE_ADMIN')"))]
    public function periodes(
        FacturationPeriodeRepository $facturationPeriodeRepository,
        BillingCalculator $billingCalculator
    ): Response {
        $periodes = $facturationPeriodeRepository->createQueryBuilder('fp')
            ->join('fp.contratLigne', 'cl')
            ->join('cl.contrat', 'c')
            ->join('cl.site', 's')
            ->orderBy('fp.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();

        // Calculer les montants pour chaque période
        $periodesAvecMontants = [];
        foreach ($periodes as $periode) {
            $calcul = $billingCalculator->calculateForPeriod($periode);
            $periodesAvecMontants[] = [
                'periode' => $periode,
                'montant' => $calcul['montant'],
                'pagesNoir' => $calcul['pagesNoir'],
                'pagesCouleur' => $calcul['pagesCouleur'],
                'details' => $calcul['details'],
            ];
        }

        return $this->render('comptabilite/periodes.html.twig', [
            'periodesAvecMontants' => $periodesAvecMontants,
        ]);
    }

    #[Route('/periode/{id}', name: 'app_comptabilite_periode_show', methods: ['GET'])]
    #[IsGranted(new Expression("is_granted('ROLE_COMPTABLE') or is_granted('ROLE_ADMIN')"))]
    public function periodeShow(
        FacturationPeriode $facturationPeriode,
        BillingCalculator $billingCalculator
    ): Response {
        $calcul = $billingCalculator->calculateForPeriod($facturationPeriode);

        return $this->render('comptabilite/periode_show.html.twig', [
            'periode' => $facturationPeriode,
            'calcul' => $calcul,
        ]);
    }
}

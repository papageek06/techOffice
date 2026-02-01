<?php

namespace App\Controller;

use App\Entity\FacturationPeriode;
use App\Repository\FacturationPeriodeRepository;
use App\Service\BillingCalculator;
use App\Service\CounterSnapshotResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/comptabilite/facturation-periode')]
#[IsGranted('ROLE_COMPTABLE')]
final class FacturationPeriodeController extends AbstractController
{
    #[Route(name: 'app_facturation_periode_index', methods: ['GET'])]
    public function index(
        FacturationPeriodeRepository $facturationPeriodeRepository,
        Request $request
    ): Response {
        $statut = $request->query->get('statut');
        $queryBuilder = $facturationPeriodeRepository->createQueryBuilder('fp')
            ->join('fp.contratLigne', 'cl')
            ->join('cl.contrat', 'c')
            ->orderBy('fp.dateDebut', 'DESC');

        if ($statut) {
            $queryBuilder->where('fp.statut = :statut')
                ->setParameter('statut', \App\Enum\StatutFacturation::from($statut));
        }

        $periodes = $queryBuilder->getQuery()->getResult();

        return $this->render('facturation_periode/index.html.twig', [
            'facturation_periodes' => $periodes,
            'statut_filtre' => $statut,
        ]);
    }

    #[Route('/{id}', name: 'app_facturation_periode_show', methods: ['GET'])]
    public function show(
        FacturationPeriode $facturationPeriode,
        BillingCalculator $billingCalculator
    ): Response {
        $calcul = $billingCalculator->calculateForPeriod($facturationPeriode);

        return $this->render('facturation_periode/show.html.twig', [
            'facturation_periode' => $facturationPeriode,
            'calcul' => $calcul,
        ]);
    }

    #[Route('/{id}/resolve-counters', name: 'app_facturation_periode_resolve_counters', methods: ['POST'])]
    public function resolveCounters(
        FacturationPeriode $facturationPeriode,
        CounterSnapshotResolver $counterSnapshotResolver,
        Request $request
    ): Response {
        if (!$this->isCsrfTokenValid('resolve_counters'.$facturationPeriode->getId(), $request->getPayload()->getString('_token'))) {
            throw $this->createAccessDeniedException('Token invalide');
        }

        try {
            $counterSnapshotResolver->resolveForPeriod($facturationPeriode);
            $this->addFlash('success', 'Compteurs résolus avec succès');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la résolution des compteurs: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_facturation_periode_show', ['id' => $facturationPeriode->getId()]);
    }

    #[Route('/{id}/validate', name: 'app_facturation_periode_validate', methods: ['POST'])]
    public function validate(
        FacturationPeriode $facturationPeriode,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        if (!$this->isCsrfTokenValid('validate'.$facturationPeriode->getId(), $request->getPayload()->getString('_token'))) {
            throw $this->createAccessDeniedException('Token invalide');
        }

        $facturationPeriode->setStatut(\App\Enum\StatutFacturation::VALIDE);
        
        // Mettre à jour la prochaine facturation de la ligne de contrat
        // La nouvelle prochaine facturation = date de fin de la période + période (au 1er jour du mois)
        $contratLigne = $facturationPeriode->getContratLigne();
        $dateFinPeriode = $facturationPeriode->getDateFin();
        $nouvelleProchaineFacturation = $this->calculateProchaineFacturation($dateFinPeriode, $contratLigne->getPeriodicite());
        $contratLigne->setProchaineFacturation($nouvelleProchaineFacturation);
        
        $entityManager->flush();

        $this->addFlash('success', 'Période validée avec succès. La prochaine facturation a été mise à jour.');

        return $this->redirectToRoute('app_facturation_periode_show', ['id' => $facturationPeriode->getId()]);
    }

    /**
     * Calcule la prochaine date de facturation après une période
     * 
     * Exemple : si dateFinPeriode = 31/01/2026 et périodicité = TRIMESTRIEL
     * Alors nouvelle prochaineFacturation = 01/05/2026 (3 mois après, 1er jour du mois)
     */
    private function calculateProchaineFacturation(\DateTimeImmutable $dateFinPeriode, \App\Enum\Periodicite $periodicite): \DateTimeImmutable
    {
        // Avancer de la période depuis la date de fin
        $prochaineFacturation = match ($periodicite) {
            \App\Enum\Periodicite::MENSUEL => $dateFinPeriode->modify('+1 month'),
            \App\Enum\Periodicite::TRIMESTRIEL => $dateFinPeriode->modify('+3 months'),
            \App\Enum\Periodicite::SEMESTRIEL => $dateFinPeriode->modify('+6 months'),
            \App\Enum\Periodicite::ANNUEL => $dateFinPeriode->modify('+1 year'),
        };
        
        // S'assurer que c'est le 1er jour du mois
        return $prochaineFacturation->modify('first day of this month');
    }

    #[Route('/{id}/invoice', name: 'app_facturation_periode_invoice', methods: ['POST'])]
    public function invoice(
        FacturationPeriode $facturationPeriode,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        if (!$this->isCsrfTokenValid('invoice'.$facturationPeriode->getId(), $request->getPayload()->getString('_token'))) {
            throw $this->createAccessDeniedException('Token invalide');
        }

        $facturationPeriode->setStatut(\App\Enum\StatutFacturation::FACTURE);
        
        // Mettre à jour la prochaine facturation de la ligne de contrat
        // La nouvelle prochaine facturation = date de fin de la période + période (au 1er jour du mois)
        $contratLigne = $facturationPeriode->getContratLigne();
        $dateFinPeriode = $facturationPeriode->getDateFin();
        $nouvelleProchaineFacturation = $this->calculateProchaineFacturation($dateFinPeriode, $contratLigne->getPeriodicite());
        $contratLigne->setProchaineFacturation($nouvelleProchaineFacturation);
        
        $entityManager->flush();

        $this->addFlash('success', 'Période marquée comme facturée. La prochaine facturation a été mise à jour.');

        return $this->redirectToRoute('app_facturation_periode_show', ['id' => $facturationPeriode->getId()]);
    }
}

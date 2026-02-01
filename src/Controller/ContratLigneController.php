<?php

namespace App\Controller;

use App\Entity\ContratLigne;
use App\Form\ContratLigneType;
use App\Repository\ContratLigneRepository;
use App\Repository\ReleveCompteurRepository;
use App\Service\BillingCalculator;
use App\Service\BillingEstimationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/comptabilite/contrat-ligne')]
#[IsGranted('ROLE_COMPTABLE')]
final class ContratLigneController extends AbstractController
{
    #[Route(name: 'app_contrat_ligne_index', methods: ['GET'])]
    public function index(ContratLigneRepository $contratLigneRepository): Response
    {
        return $this->render('contrat_ligne/index.html.twig', [
            'contrat_lignes' => $contratLigneRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_contrat_ligne_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $contratLigne = new ContratLigne();
        
        // Si un contrat_id est fourni, pré-remplir le contrat
        $contratId = $request->query->get('contrat_id');
        if ($contratId) {
            $contrat = $entityManager->getRepository(\App\Entity\Contrat::class)->find($contratId);
            if ($contrat) {
                $contratLigne->setContrat($contrat);
            }
        }
        
        $form = $this->createForm(ContratLigneType::class, $contratLigne);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($contratLigne);
            $entityManager->flush();

            // Rediriger vers la page de configuration du contrat si contrat_id était présent
            if ($contratId) {
                return $this->redirectToRoute('app_contrat_configure', ['id' => $contratId], Response::HTTP_SEE_OTHER);
            }

            return $this->redirectToRoute('app_contrat_ligne_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('contrat_ligne/new.html.twig', [
            'contrat_ligne' => $contratLigne,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_contrat_ligne_show', methods: ['GET'])]
    public function show(
        ContratLigne $contratLigne,
        BillingEstimationService $billingEstimationService,
        BillingCalculator $billingCalculator,
        ReleveCompteurRepository $releveCompteurRepository
    ): Response {
        // Calculer l'estimation pour cette ligne
        try {
            $estimationData = $billingEstimationService->calculateEstimation($contratLigne);
        } catch (\Exception $e) {
            $estimationData = [
                'compteursActuels' => [],
                'derniereFacture' => null,
                'estimation' => [
                    'estimations' => [],
                    'montantEstime' => 0,
                    'pagesNoirTotal' => 0,
                    'pagesCouleurTotal' => 0,
                    'prochaineFacturation' => $contratLigne->getProchaineFacturation(),
                ],
            ];
        }

        // Récupérer le dernier compteur pour chaque affectation
        $affectationsAvecCompteurs = [];
        foreach ($contratLigne->getAffectationsMateriel() as $affectation) {
            $imprimante = $affectation->getImprimante();
            $dernierReleve = $releveCompteurRepository->createQueryBuilder('r')
                ->where('r.imprimante = :imprimante')
                ->orderBy('r.dateReleve', 'DESC')
                ->setMaxResults(1)
                ->setParameter('imprimante', $imprimante)
                ->getQuery()
                ->getOneOrNullResult();

            $affectationsAvecCompteurs[] = [
                'affectation' => $affectation,
                'dernierReleve' => $dernierReleve,
            ];
        }

        // Calculer le total de la dernière facture
        $montantDerniereFacture = 0;
        if ($estimationData['derniereFacture']) {
            $calcul = $billingCalculator->calculateForPeriod($estimationData['derniereFacture']['periode']);
            $montantDerniereFacture = $calcul['montant'];
        }

        return $this->render('contrat_ligne/show.html.twig', [
            'contrat_ligne' => $contratLigne,
            'estimationData' => $estimationData,
            'affectationsAvecCompteurs' => $affectationsAvecCompteurs,
            'montantDerniereFacture' => $montantDerniereFacture,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_contrat_ligne_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ContratLigne $contratLigne, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ContratLigneType::class, $contratLigne);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_contrat_ligne_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('contrat_ligne/edit.html.twig', [
            'contrat_ligne' => $contratLigne,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_contrat_ligne_delete', methods: ['POST'])]
    public function delete(Request $request, ContratLigne $contratLigne, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$contratLigne->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($contratLigne);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_contrat_ligne_index', [], Response::HTTP_SEE_OTHER);
    }
}

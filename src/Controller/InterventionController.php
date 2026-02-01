<?php

namespace App\Controller;

use App\Entity\Intervention;
use App\Entity\Site;
use App\Form\InterventionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/intervention')]
final class InterventionController extends AbstractController
{
    #[Route(name: 'app_intervention_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $interventions = $entityManager
            ->getRepository(Intervention::class)
            ->findAll();

        return $this->render('intervention/index.html.twig', [
            'interventions' => $interventions,
        ]);
    }

    #[Route('/new', name: 'app_intervention_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $intervention = new Intervention();
        $intervention->setDescription('');
        $site = null;
        $siteId = $request->query->getInt('site_id');
        if ($siteId > 0) {
            $site = $entityManager->getRepository(Site::class)->find($siteId);
            if ($site) {
                $imprimantes = $site->getImprimantes();
                if ($imprimantes->count() === 1) {
                    $intervention->setImprimante($imprimantes->first());
                }
                $intervention->setStatut(\App\Enum\StatutIntervention::OUVERTE);
                $intervention->setUtilisateur($this->getUser());
            }
        }

        $form = $this->createForm(InterventionType::class, $intervention, [
            'site' => $site,
            'from_site' => $site !== null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($intervention->getUtilisateur() === null) {
                $intervention->setUtilisateur($this->getUser());
            }
            $entityManager->persist($intervention);
            $entityManager->flush();

            return $this->redirectToRoute('app_intervention_index', [], Response::HTTP_SEE_OTHER);
        }

        $stockEntrepriseParPiece = [];
        if ($site && $intervention->getImprimante()) {
            $stockEntrepriseParPiece = $this->getStockEntreprisePourModele($entityManager, $intervention->getImprimante()->getModele()->getId());
        }

        return $this->render('intervention/new.html.twig', [
            'intervention' => $intervention,
            'form' => $form,
            'site' => $site,
            'stock_entreprise' => $stockEntrepriseParPiece,
        ]);
    }

    private function getStockEntreprisePourModele(EntityManagerInterface $em, int $modeleId): array
    {
        $modele = $em->getRepository(\App\Entity\Modele::class)->find($modeleId);
        if (!$modele) {
            return [];
        }
        $stockLocationRepo = $em->getRepository(\App\Entity\StockLocation::class);
        $stocks = $stockLocationRepo->findEntrepriseStocks();
        if (empty($stocks)) {
            return [];
        }
        $stock = $stocks[0];
        $result = [];
        foreach ($stock->getStockItems() as $item) {
            $piece = $item->getPiece();
            $pm = $em->getRepository(\App\Entity\PieceModele::class)
                ->findOneBy(['piece' => $piece, 'modele' => $modele]);
            if (!$pm) {
                continue;
            }
            $role = $pm->getRole()->value;
            $isTonerOuBac = str_starts_with($role, 'TONER_') || $role === 'BAC_RECUP';
            if ($isTonerOuBac) {
                $result[$piece->getId()] = [
                    'quantite' => $item->getQuantite(),
                    'reference' => $piece->getReference(),
                    'designation' => $piece->getDesignation(),
                    'role' => $role,
                ];
            }
        }
        return $result;
    }

    #[Route('/{id}', name: 'app_intervention_show', methods: ['GET'])]
    public function show(Intervention $intervention): Response
    {
        return $this->render('intervention/show.html.twig', [
            'intervention' => $intervention,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_intervention_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Intervention $intervention, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(InterventionType::class, $intervention, [
            'site' => null,
            'from_site' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_intervention_index', [], Response::HTTP_SEE_OTHER);
        }

        $stockEntrepriseParPiece = [];
        if ($intervention->getImprimante()) {
            $stockEntrepriseParPiece = $this->getStockEntreprisePourModele($entityManager, $intervention->getImprimante()->getModele()->getId());
        }

        return $this->render('intervention/edit.html.twig', [
            'intervention' => $intervention,
            'form' => $form,
            'stock_entreprise' => $stockEntrepriseParPiece,
        ]);
    }

    #[Route('/{id}', name: 'app_intervention_delete', methods: ['POST'])]
    public function delete(Request $request, Intervention $intervention, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$intervention->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($intervention);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_intervention_index', [], Response::HTTP_SEE_OTHER);
    }
}

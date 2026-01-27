<?php

namespace App\Controller;

use App\Entity\AffectationMateriel;
use App\Form\AffectationMaterielType;
use App\Repository\AffectationMaterielRepository;
use App\Service\AffectationMaterielManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/comptabilite/affectation-materiel')]
#[IsGranted('ROLE_COMPTABLE')]
final class AffectationMaterielController extends AbstractController
{
    #[Route(name: 'app_affectation_materiel_index', methods: ['GET'])]
    public function index(AffectationMaterielRepository $affectationMaterielRepository): Response
    {
        return $this->render('affectation_materiel/index.html.twig', [
            'affectations' => $affectationMaterielRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_affectation_materiel_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        AffectationMaterielManager $affectationMaterielManager
    ): Response {
        $affectationMateriel = new AffectationMateriel();
        $form = $this->createForm(AffectationMaterielType::class, $affectationMateriel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Utiliser le service pour créer l'affectation (gère la clôture de l'ancienne)
            $affectationMaterielManager->createAffectation(
                $affectationMateriel->getContratLigne(),
                $affectationMateriel->getImprimante(),
                $affectationMateriel->getDateDebut(),
                $affectationMateriel->getTypeAffectation(),
                $affectationMateriel->getReason()
            );

            return $this->redirectToRoute('app_affectation_materiel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('affectation_materiel/new.html.twig', [
            'affectation_materiel' => $affectationMateriel,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_affectation_materiel_show', methods: ['GET'])]
    public function show(AffectationMateriel $affectationMateriel): Response
    {
        return $this->render('affectation_materiel/show.html.twig', [
            'affectation_materiel' => $affectationMateriel,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_affectation_materiel_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        AffectationMateriel $affectationMateriel,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(AffectationMaterielType::class, $affectationMateriel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_affectation_materiel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('affectation_materiel/edit.html.twig', [
            'affectation_materiel' => $affectationMateriel,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_affectation_materiel_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        AffectationMateriel $affectationMateriel,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$affectationMateriel->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($affectationMateriel);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_affectation_materiel_index', [], Response::HTTP_SEE_OTHER);
    }
}

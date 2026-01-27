<?php

namespace App\Controller;

use App\Entity\ContratLigne;
use App\Form\ContratLigneType;
use App\Repository\ContratLigneRepository;
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
        $form = $this->createForm(ContratLigneType::class, $contratLigne);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($contratLigne);
            $entityManager->flush();

            return $this->redirectToRoute('app_contrat_ligne_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('contrat_ligne/new.html.twig', [
            'contrat_ligne' => $contratLigne,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_contrat_ligne_show', methods: ['GET'])]
    public function show(ContratLigne $contratLigne): Response
    {
        return $this->render('contrat_ligne/show.html.twig', [
            'contrat_ligne' => $contratLigne,
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

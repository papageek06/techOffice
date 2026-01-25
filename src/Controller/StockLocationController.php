<?php

namespace App\Controller;

use App\Entity\StockLocation;
use App\Form\StockLocationType;
use App\Repository\StockLocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/stock-location')]
final class StockLocationController extends AbstractController
{
    #[Route(name: 'app_stock_location_index', methods: ['GET'])]
    public function index(StockLocationRepository $stockLocationRepository): Response
    {
        return $this->render('stock_location/index.html.twig', [
            'stock_locations' => $stockLocationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_stock_location_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $stockLocation = new StockLocation();
        $form = $this->createForm(StockLocationType::class, $stockLocation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($stockLocation);
            $entityManager->flush();

            return $this->redirectToRoute('app_stock_location_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('stock_location/new.html.twig', [
            'stock_location' => $stockLocation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_stock_location_show', methods: ['GET'])]
    public function show(StockLocation $stockLocation): Response
    {
        return $this->render('stock_location/show.html.twig', [
            'stock_location' => $stockLocation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_stock_location_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, StockLocation $stockLocation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(StockLocationType::class, $stockLocation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_stock_location_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('stock_location/edit.html.twig', [
            'stock_location' => $stockLocation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_stock_location_delete', methods: ['POST'])]
    public function delete(Request $request, StockLocation $stockLocation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$stockLocation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($stockLocation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_stock_location_index', [], Response::HTTP_SEE_OTHER);
    }
}

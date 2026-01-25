<?php

namespace App\Controller;

use App\Entity\StockItem;
use App\Form\StockItemType;
use App\Repository\StockItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/stock-item')]
final class StockItemController extends AbstractController
{
    #[Route(name: 'app_stock_item_index', methods: ['GET'])]
    public function index(StockItemRepository $stockItemRepository): Response
    {
        return $this->render('stock_item/index.html.twig', [
            'stock_items' => $stockItemRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_stock_item_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $stockItem = new StockItem();
        $form = $this->createForm(StockItemType::class, $stockItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($stockItem);
            $entityManager->flush();

            return $this->redirectToRoute('app_stock_item_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('stock_item/new.html.twig', [
            'stock_item' => $stockItem,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_stock_item_show', methods: ['GET'])]
    public function show(StockItem $stockItem): Response
    {
        return $this->render('stock_item/show.html.twig', [
            'stock_item' => $stockItem,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_stock_item_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, StockItem $stockItem, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(StockItemType::class, $stockItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_stock_item_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('stock_item/edit.html.twig', [
            'stock_item' => $stockItem,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_stock_item_delete', methods: ['POST'])]
    public function delete(Request $request, StockItem $stockItem, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$stockItem->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($stockItem);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_stock_item_index', [], Response::HTTP_SEE_OTHER);
    }
}

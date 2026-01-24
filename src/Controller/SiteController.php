<?php

namespace App\Controller;

use App\Entity\Site;
use App\Form\SiteType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/site')]
final class SiteController extends AbstractController
{
    #[Route(name: 'app_site_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $search = $request->query->get('search', '');
        $isAjax = $request->isXmlHttpRequest() || $request->query->getBoolean('ajax', false);
        
        // Charger les sites avec leurs imprimantes, modèles, fabricants, relevés et états consommables
        $qb = $entityManager
            ->getRepository(Site::class)
            ->createQueryBuilder('s')
            ->leftJoin('s.imprimantes', 'i')
            ->leftJoin('i.modele', 'm')
            ->leftJoin('m.fabricant', 'f')
            ->leftJoin('i.relevesCompteur', 'r')
            ->leftJoin('i.etatsConsommable', 'e')
            ->addSelect('i')
            ->addSelect('m')
            ->addSelect('f')
            ->addSelect('r')
            ->addSelect('e');

        // Filtrer par nom de site ou numéro de série d'imprimante
        if (!empty($search)) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('LOWER(s.nomSite)', 'LOWER(:search)'),
                    $qb->expr()->like('LOWER(i.numeroSerie)', 'LOWER(:search)')
                )
            )
            ->setParameter('search', '%' . $search . '%');
        }

        $sites = $qb
            ->orderBy('s.nomSite', 'ASC')
            ->addOrderBy('i.numeroSerie', 'ASC')
            ->getQuery()
            ->getResult();
        
        // Supprimer les doublons de sites (peuvent apparaître si plusieurs imprimantes correspondent)
        $uniqueSites = [];
        foreach ($sites as $site) {
            if (!isset($uniqueSites[$site->getId()])) {
                $uniqueSites[$site->getId()] = $site;
            }
        }
        $sites = array_values($uniqueSites);

        // Si requête AJAX, retourner uniquement le contenu des résultats
        if ($isAjax) {
            return $this->render('site/_results.html.twig', [
                'sites' => $sites,
                'search' => $search,
            ]);
        }

        return $this->render('site/index.html.twig', [
            'sites' => $sites,
            'search' => $search,
        ]);
    }

    #[Route('/new', name: 'app_site_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $site = new Site();
        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($site);
            $entityManager->flush();

            return $this->redirectToRoute('app_site_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('site/new.html.twig', [
            'site' => $site,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_site_show', methods: ['GET'])]
    public function show(Site $site): Response
    {
        return $this->render('site/show.html.twig', [
            'site' => $site,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_site_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Site $site, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_site_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('site/edit.html.twig', [
            'site' => $site,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_site_delete', methods: ['POST'])]
    public function delete(Request $request, Site $site, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$site->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($site);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_site_index', [], Response::HTTP_SEE_OTHER);
    }
}

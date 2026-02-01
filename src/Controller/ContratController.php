<?php

namespace App\Controller;

use App\Entity\Contrat;
use App\Form\ContratType;
use App\Repository\ContratRepository;
use App\Service\BillingCalculator;
use App\Service\BillingEstimationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/comptabilite/contrat')]
#[IsGranted('ROLE_COMPTABLE')]
final class ContratController extends AbstractController
{
    #[Route(name: 'app_contrat_index', methods: ['GET'])]
    public function index(ContratRepository $contratRepository): Response
    {
        return $this->render('contrat/index.html.twig', [
            'contrats' => $contratRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_contrat_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $contrat = new Contrat();
        $form = $this->createForm(ContratType::class, $contrat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($contrat);
            $entityManager->flush();

            // Vérifier si l'utilisateur veut continuer la configuration
            $continueConfig = $request->request->get('continue_config', false);
            if ($continueConfig) {
                return $this->redirectToRoute('app_contrat_configure', ['id' => $contrat->getId()], Response::HTTP_SEE_OTHER);
            }

            return $this->redirectToRoute('app_contrat_show', ['id' => $contrat->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('contrat/new.html.twig', [
            'contrat' => $contrat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/configure', name: 'app_contrat_configure', methods: ['GET', 'POST'])]
    public function configure(
        Request $request,
        Contrat $contrat,
        EntityManagerInterface $entityManager
    ): Response {
        // Cette route permet de configurer les lignes, périodes, affectations et compteurs de départ
        // Le formulaire sera géré via AJAX ou plusieurs étapes
        
        if ($request->isMethod('POST')) {
            // Traitement des données de configuration
            $action = $request->request->get('action');
            
            if ($action === 'save_ligne') {
                // Créer ou modifier une ligne de contrat
                // Cette partie sera gérée via un formulaire séparé ou AJAX
            } elseif ($action === 'save_affectation') {
                // Créer ou modifier une affectation
            } elseif ($action === 'save_compteur_depart') {
                // Enregistrer un compteur de départ pour une machine d'occasion/remplacement
                $imprimanteId = $request->request->get('imprimante_id');
                $compteurNoir = $request->request->get('compteur_noir');
                $compteurCouleur = $request->request->get('compteur_couleur');
                $dateReleveStr = $request->request->get('date_releve');
                
                if ($imprimanteId && $compteurNoir !== null && $dateReleveStr) {
                    $imprimante = $entityManager->getRepository(\App\Entity\Imprimante::class)->find($imprimanteId);
                    if ($imprimante) {
                        try {
                            // Créer la date avec l'heure (8h du matin par défaut)
                            $dateReleve = new \DateTimeImmutable($dateReleveStr . ' 08:00:00');
                            
                            // Vérifier si un relevé existe déjà pour cette date (même jour, peu importe l'heure)
                            $dateDebutJour = $dateReleve->setTime(0, 0, 0);
                            $dateFinJour = $dateReleve->setTime(23, 59, 59);
                            
                            $releveExistant = $entityManager->getRepository(\App\Entity\ReleveCompteur::class)
                                ->createQueryBuilder('r')
                                ->where('r.imprimante = :imprimante')
                                ->andWhere('r.dateReleve >= :dateDebut')
                                ->andWhere('r.dateReleve <= :dateFin')
                                ->setParameter('imprimante', $imprimante)
                                ->setParameter('dateDebut', $dateDebutJour)
                                ->setParameter('dateFin', $dateFinJour)
                                ->getQuery()
                                ->getOneOrNullResult();
                            
                            if ($releveExistant) {
                                // Mettre à jour le relevé existant
                                $releveExistant->setCompteurNoir((int) $compteurNoir);
                                $releveExistant->setCompteurCouleur($compteurCouleur ? (int) $compteurCouleur : null);
                                $releveExistant->setSource('manuel');
                                $releveExistant->setDateReceptionRapport(new \DateTimeImmutable());
                                $this->addFlash('success', 'Compteur de départ mis à jour avec succès.');
                            } else {
                                // Créer un nouveau relevé
                                $releve = new \App\Entity\ReleveCompteur();
                                $releve->setImprimante($imprimante);
                                $releve->setDateReleve($dateReleve);
                                $releve->setCompteurNoir((int) $compteurNoir);
                                $releve->setCompteurCouleur($compteurCouleur ? (int) $compteurCouleur : null);
                                $releve->setCompteurFax(0);
                                $releve->setSource('manuel');
                                $releve->setDateReceptionRapport(new \DateTimeImmutable());
                                
                                $entityManager->persist($releve);
                                $this->addFlash('success', 'Compteur de départ enregistré avec succès.');
                            }
                            
                            $entityManager->flush();
                        } catch (\Exception $e) {
                            $this->addFlash('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage());
                        }
                    } else {
                        $this->addFlash('error', 'Imprimante introuvable.');
                    }
                } else {
                    $this->addFlash('error', 'Veuillez remplir tous les champs obligatoires.');
                }
            }
            
            return $this->redirectToRoute('app_contrat_configure', ['id' => $contrat->getId()], Response::HTTP_SEE_OTHER);
        }

        // Récupérer les sites du client pour les lignes de contrat
        $sites = $entityManager->getRepository(\App\Entity\Site::class)
            ->findBy(['client' => $contrat->getClient(), 'actif' => true]);

        // Récupérer les imprimantes des sites du client
        $imprimantes = [];
        foreach ($sites as $site) {
            $imprimantesSite = $entityManager->getRepository(\App\Entity\Imprimante::class)
                ->findBy(['site' => $site]);
            $imprimantes = array_merge($imprimantes, $imprimantesSite);
        }

        return $this->render('contrat/configure.html.twig', [
            'contrat' => $contrat,
            'sites' => $sites,
            'imprimantes' => $imprimantes,
        ]);
    }

    #[Route('/{id}', name: 'app_contrat_show', methods: ['GET'])]
    public function show(
        Contrat $contrat, 
        BillingCalculator $billingCalculator,
        BillingEstimationService $billingEstimationService
    ): Response {
        // Préparer les données de facturation pour chaque ligne de contrat
        $lignesAvecFacturation = [];
        foreach ($contrat->getContratLignes() as $ligne) {
            $periodesAvecMontants = [];
            foreach ($ligne->getFacturationPeriodes() as $periode) {
                $calcul = $billingCalculator->calculateForPeriod($periode);
                $periodesAvecMontants[] = [
                    'periode' => $periode,
                    'montant' => $calcul['montant'],
                    'pagesNoir' => $calcul['pagesNoir'],
                    'pagesCouleur' => $calcul['pagesCouleur'],
                    'details' => $calcul['details'],
                ];
            }
            
            // Calculer l'estimation pour cette ligne
            try {
                $estimationData = $billingEstimationService->calculateEstimation($ligne);
            } catch (\Exception $e) {
                // En cas d'erreur, utiliser des valeurs par défaut
                $estimationData = [
                    'compteursActuels' => [],
                    'derniereFacture' => null,
                    'estimation' => [
                        'estimations' => [],
                        'montantEstime' => 0,
                        'pagesNoirTotal' => 0,
                        'pagesCouleurTotal' => 0,
                        'prochaineFacturation' => $ligne->getProchaineFacturation(),
                    ],
                ];
            }
            
            $lignesAvecFacturation[] = [
                'ligne' => $ligne,
                'periodes' => $periodesAvecMontants,
                'compteursActuels' => $estimationData['compteursActuels'] ?? [],
                'derniereFacture' => $estimationData['derniereFacture'] ?? null,
                'estimation' => $estimationData['estimation'] ?? [
                    'estimations' => [],
                    'montantEstime' => 0,
                    'pagesNoirTotal' => 0,
                    'pagesCouleurTotal' => 0,
                    'prochaineFacturation' => $ligne->getProchaineFacturation(),
                ],
            ];
        }

        return $this->render('contrat/show.html.twig', [
            'contrat' => $contrat,
            'lignesAvecFacturation' => $lignesAvecFacturation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_contrat_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Contrat $contrat, EntityManagerInterface $entityManager, BillingCalculator $billingCalculator): Response
    {
        $form = $this->createForm(ContratType::class, $contrat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Vérifier si l'utilisateur veut continuer la configuration
            $continueConfig = $request->request->get('continue_config', false);
            if ($continueConfig) {
                return $this->redirectToRoute('app_contrat_configure', ['id' => $contrat->getId()], Response::HTTP_SEE_OTHER);
            }

            return $this->redirectToRoute('app_contrat_show', ['id' => $contrat->getId()], Response::HTTP_SEE_OTHER);
        }

        // Préparer les données de facturation pour chaque ligne de contrat
        $lignesAvecFacturation = [];
        foreach ($contrat->getContratLignes() as $ligne) {
            $periodesAvecMontants = [];
            foreach ($ligne->getFacturationPeriodes() as $periode) {
                $calcul = $billingCalculator->calculateForPeriod($periode);
                $periodesAvecMontants[] = [
                    'periode' => $periode,
                    'montant' => $calcul['montant'],
                    'pagesNoir' => $calcul['pagesNoir'],
                    'pagesCouleur' => $calcul['pagesCouleur'],
                    'details' => $calcul['details'],
                ];
            }
            
            $lignesAvecFacturation[] = [
                'ligne' => $ligne,
                'periodes' => $periodesAvecMontants,
            ];
        }

        return $this->render('contrat/edit.html.twig', [
            'contrat' => $contrat,
            'form' => $form,
            'lignesAvecFacturation' => $lignesAvecFacturation,
        ]);
    }

    #[Route('/{id}', name: 'app_contrat_delete', methods: ['POST'])]
    public function delete(Request $request, Contrat $contrat, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$contrat->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($contrat);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_contrat_index', [], Response::HTTP_SEE_OTHER);
    }
}

<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Fabricant;
use App\Entity\Modele;
use App\Entity\Piece;
use App\Entity\PieceModele;
use App\Entity\Site;
use App\Entity\StockItem;
use App\Entity\StockLocation;
use App\Enum\PieceRoleModele;
use App\Enum\PieceType;
use App\Enum\StockLocationType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Fixtures pour le système de stock
 * Nécessite que Client, Site, Fabricant, Modele existent déjà
 */
class StockFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Récupérer ou créer un site entreprise (client avec nom "Entreprise" ou similaire)
        $clientEntreprise = $manager->getRepository(Client::class)->findOneBy(['nom' => 'Entreprise']);
        if (!$clientEntreprise) {
            // Créer un client entreprise si nécessaire
            $clientEntreprise = new Client();
            $clientEntreprise->setNom('Entreprise');
            $clientEntreprise->setActif(true);
            $manager->persist($clientEntreprise);
        }

        $siteEntreprise = $manager->getRepository(Site::class)->findOneBy([
            'client' => $clientEntreprise,
            'nomSite' => 'Atelier Principal'
        ]);

        if (!$siteEntreprise) {
            $siteEntreprise = new Site();
            $siteEntreprise->setClient($clientEntreprise);
            $siteEntreprise->setNomSite('Atelier Principal');
            $siteEntreprise->setPrincipal(true);
            $siteEntreprise->setActif(true);
            $manager->persist($siteEntreprise);
        }

        // Créer un stock ENTREPRISE
        $stockEntreprise = new StockLocation();
        $stockEntreprise->setSite($siteEntreprise);
        $stockEntreprise->setType(StockLocationType::ENTREPRISE);
        $stockEntreprise->setNomStock('Atelier');
        $stockEntreprise->setActif(true);
        $manager->persist($stockEntreprise);

        // Récupérer un site client existant (premier site actif qui n'est pas l'entreprise)
        $siteClient = $manager->getRepository(Site::class)->createQueryBuilder('s')
            ->join('s.client', 'c')
            ->where('c.nom != :entreprise')
            ->andWhere('s.actif = true')
            ->setParameter('entreprise', 'Entreprise')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $stockClient = null;
        if ($siteClient) {
            // Créer un stock CLIENT
            $stockClient = new StockLocation();
            $stockClient->setSite($siteClient);
            $stockClient->setType(StockLocationType::CLIENT);
            $stockClient->setNomStock('Stock Client');
            $stockClient->setActif(true);
            $manager->persist($stockClient);
        }

        // Récupérer ou créer un fabricant et modèle
        $fabricant = $manager->getRepository(Fabricant::class)->findOneBy(['nomFabricant' => 'RICOH']);
        if (!$fabricant) {
            $fabricant = new Fabricant();
            $fabricant->setNomFabricant('RICOH');
            $manager->persist($fabricant);
        }

        $modele = $manager->getRepository(Modele::class)->findOneBy([
            'fabricant' => $fabricant,
            'referenceModele' => 'MP C2004'
        ]);

        if (!$modele) {
            $modele = new Modele();
            $modele->setFabricant($fabricant);
            $modele->setReferenceModele('MP C2004');
            $modele->setCouleur(true);
            $manager->persist($modele);
        }

        // Créer des pièces
        $tonerNoir = new Piece();
        $tonerNoir->setReference('TONER-K-MPC2004');
        $tonerNoir->setDesignation('Toner Noir MP C2004');
        $tonerNoir->setTypePiece(PieceType::TONER);
        $tonerNoir->setCouleur('K');
        $tonerNoir->setActif(true);
        $manager->persist($tonerNoir);

        $tonerCyan = new Piece();
        $tonerCyan->setReference('TONER-C-MPC2004');
        $tonerCyan->setDesignation('Toner Cyan MP C2004');
        $tonerCyan->setTypePiece(PieceType::TONER);
        $tonerCyan->setCouleur('C');
        $tonerCyan->setActif(true);
        $manager->persist($tonerCyan);

        $bacRecup = new Piece();
        $bacRecup->setReference('BAC-RECUP-MPC2004');
        $bacRecup->setDesignation('Bac de récupération MP C2004');
        $bacRecup->setTypePiece(PieceType::BAC_RECUP);
        $bacRecup->setActif(true);
        $manager->persist($bacRecup);

        // Lier les pièces au modèle
        $pmTonerK = new PieceModele();
        $pmTonerK->setPiece($tonerNoir);
        $pmTonerK->setModele($modele);
        $pmTonerK->setRole(PieceRoleModele::TONER_K);
        $manager->persist($pmTonerK);

        $pmTonerC = new PieceModele();
        $pmTonerC->setPiece($tonerCyan);
        $pmTonerC->setModele($modele);
        $pmTonerC->setRole(PieceRoleModele::TONER_C);
        $manager->persist($pmTonerC);

        $pmBacRecup = new PieceModele();
        $pmBacRecup->setPiece($bacRecup);
        $pmBacRecup->setModele($modele);
        $pmBacRecup->setRole(PieceRoleModele::BAC_RECUP);
        $manager->persist($pmBacRecup);

        // Créer des StockItem
        $siTonerK = new StockItem();
        $siTonerK->setStockLocation($stockEntreprise);
        $siTonerK->setPiece($tonerNoir);
        $siTonerK->setQuantite(10);
        $siTonerK->setSeuilAlerte(2);
        $manager->persist($siTonerK);

        $siTonerC = new StockItem();
        $siTonerC->setStockLocation($stockEntreprise);
        $siTonerC->setPiece($tonerCyan);
        $siTonerC->setQuantite(8);
        $siTonerC->setSeuilAlerte(2);
        $manager->persist($siTonerC);

        if ($stockClient) {
            $siBacRecupClient = new StockItem();
            $siBacRecupClient->setStockLocation($stockClient);
            $siBacRecupClient->setPiece($bacRecup);
            $siBacRecupClient->setQuantite(3);
            $siBacRecupClient->setSeuilAlerte(1);
            $manager->persist($siBacRecupClient);
        }

        $manager->flush();
    }
}

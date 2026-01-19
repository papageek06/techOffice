<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\Site;
use App\Entity\Fabricant;
use App\Entity\Modele;
use App\Entity\Imprimante;
use App\Entity\ReleveCompteur;
use App\Entity\EtatConsommable;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

class ImportCsvService
{
    public function __construct(
        private ManagerRegistry $registry,
        private ?LoggerInterface $logger = null
    ) {
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return $this->registry->getManager();
    }

    /**
     * Importe un fichier CSV
     * 
     * @return array{success: int, errors: array<string>, skipped: int}
     */
    public function import(string $filePath): array
    {
        $success = 0;
        $errors = [];
        $skipped = 0;

        if (!file_exists($filePath) || !is_readable($filePath)) {
            return [
                'success' => 0,
                'errors' => ["Le fichier n'existe pas ou n'est pas accessible : $filePath"],
                'skipped' => 0
            ];
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return [
                'success' => 0,
                'errors' => ["Impossible d'ouvrir le fichier : $filePath"],
                'skipped' => 0
            ];
        }

        // Lire l'en-tête
        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            return [
                'success' => 0,
                'errors' => ["Impossible de lire l'en-tête du fichier CSV"],
                'skipped' => 0
            ];
        }

        $lineNumber = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;
            
            try {
                $data = array_combine($header, $row);
                if ($data === false) {
                    $skipped++;
                    continue;
                }
                
                // Ignorer les lignes de totaux (pas de SERIAL_NUMBER ou NAME contient "Total")
                $serialNumber = trim($data['SERIAL_NUMBER'] ?? '');
                $name = trim($data['NAME'] ?? '');
                
                if (empty($serialNumber) || str_contains($name, 'Total')) {
                    $skipped++;
                    continue;
                }

                // Valider les données essentielles : CUSTOMER, MODEL, BRAND, READING_DATE
                $customer = trim($data['CUSTOMER'] ?? '');
                $model = trim($data['MODEL'] ?? '');
                $brand = trim($data['BRAND'] ?? '');
                $readingDate = trim($data['READING_DATE'] ?? '');
                
                if (empty($customer) || empty($model) || empty($brand) || empty($readingDate)) {
                    $skipped++;
                    continue;
                }

                // Traiter la ligne
                try {
                    $this->processRow($data);
                    $this->getEntityManager()->flush();
                    $success++;
                } catch (UniqueConstraintViolationException $e) {
                    // Réinitialiser l'EntityManager après une erreur de contrainte
                    $this->resetEntityManager();
                    $errors[] = "Ligne $lineNumber : Doublon détecté (contrainte unique violée)";
                    $skipped++;
                } catch (\Exception $e) {
                    // Réinitialiser l'EntityManager après une erreur
                    $this->resetEntityManager();
                    $errors[] = "Ligne $lineNumber : " . $e->getMessage();
                    if ($this->logger) {
                        $this->logger->error("Erreur ligne $lineNumber", [
                            'exception' => $e,
                            'data' => $data ?? null
                        ]);
                    }
                }
            } catch (\Exception $e) {
                $errors[] = "Ligne $lineNumber : Erreur de parsing - " . $e->getMessage();
                if ($this->logger) {
                    $this->logger->error("Erreur parsing ligne $lineNumber", [
                        'exception' => $e
                    ]);
                }
            }
        }

        fclose($handle);

        return [
            'success' => $success,
            'errors' => $errors,
            'skipped' => $skipped
        ];
    }

    private function processRow(array $data): void
    {
        // CUSTOMER = nom du site (pas le client)
        $nomSite = trim($data['CUSTOMER']);
        
        // 1. Créer un client avec le même nom que le site (ou utiliser un client générique)
        // Pour l'instant, on crée un client avec le nom du site
        $client = $this->findOrCreateClient($nomSite);

        // 2. Trouver ou créer le Site avec le nom du CUSTOMER
        $site = $this->findOrCreateSite($client, $nomSite);

        // 3. Trouver ou créer le Fabricant (BRAND = RICOH, etc.)
        $fabricant = $this->findOrCreateFabricant(trim($data['BRAND']));

        // 4. Trouver ou créer le Modèle (MODEL = modèle imprimante, utiliser MODEL en priorité, sinon NAME)
        $modelName = trim($data['MODEL'] ?? $data['NAME'] ?? '');
        $modele = $this->findOrCreateModele($fabricant, $modelName, $data['COLOR_MONO'] ?? 'Mono');

        // 5. Trouver ou créer l'Imprimante
        // Utiliser IPADDRESS en priorité pour identifier, sinon SERIAL_NUMBER
        $imprimante = $this->findOrCreateImprimante(
            $site,
            $modele,
            trim($data['IPADDRESS'] ?? '') ?: null,  // IPADDRESS pour identification réseau
            trim($data['SERIAL_NUMBER'] ?? '') ?: null,  // SERIAL_NUMBER = numéro de série machine
            trim($data['LOCATION'] ?? '') ?: null,
            $data['MANAGED'] ?? 'True'
        );

        // 6. Créer le ReleveCompteur
        $this->createReleveCompteur($imprimante, $data);

        // 7. Créer l'EtatConsommable
        $this->createEtatConsommable($imprimante, $data);
    }

    private function resetEntityManager(): void
    {
        $em = $this->getEntityManager();
        if (!$em->isOpen()) {
            $this->registry->resetManager();
        } else {
            $em->clear();
        }
    }

    private function findOrCreateClient(string $nom): Client
    {
        $em = $this->getEntityManager();
        $client = $em->getRepository(Client::class)
            ->findOneBy(['nom' => $nom]);

        if (!$client) {
            $client = new Client();
            $client->setNom($nom);
            $client->setActif(true);
            $em->persist($client);
        }

        return $client;
    }

    private function findOrCreateSite(Client $client, string $nomSite): Site
    {
        $em = $this->getEntityManager();
        $site = $em->getRepository(Site::class)
            ->findOneBy([
                'client' => $client,
                'nomSite' => $nomSite
            ]);

        if (!$site) {
            $site = new Site();
            $site->setClient($client);
            $site->setNomSite($nomSite);
            // Marquer comme principal si c'est le premier site du client
            $existingSites = $em->getRepository(Site::class)
                ->findBy(['client' => $client]);
            $site->setPrincipal(count($existingSites) === 0);
            $site->setActif(true);
            $em->persist($site);
        }

        return $site;
    }

    private function findOrCreateFabricant(string $nom): Fabricant
    {
        $em = $this->getEntityManager();
        $fabricant = $em->getRepository(Fabricant::class)
            ->findOneBy(['nomFabricant' => $nom]);

        if (!$fabricant) {
            $fabricant = new Fabricant();
            $fabricant->setNomFabricant($nom);
            $em->persist($fabricant);
        }

        return $fabricant;
    }

    private function findOrCreateModele(Fabricant $fabricant, string $reference, string $colorMono): Modele
    {
        $em = $this->getEntityManager();
        $modele = $em->getRepository(Modele::class)
            ->findOneBy([
                'fabricant' => $fabricant,
                'referenceModele' => $reference
            ]);

        if (!$modele) {
            $modele = new Modele();
            $modele->setFabricant($fabricant);
            $modele->setReferenceModele($reference);
            $modele->setCouleur(strtolower($colorMono) === 'couleur');
            $em->persist($modele);
        }

        return $modele;
    }

    private function findOrCreateImprimante(
        Site $site,
        Modele $modele,
        ?string $ipAddress,
        ?string $numeroSerie,
        ?string $emplacement,
        string $managed
    ): Imprimante {
        $em = $this->getEntityManager();
        
        // Chercher d'abord par IP (plus fiable)
        if ($ipAddress) {
            $imprimante = $em->getRepository(Imprimante::class)
                ->findOneBy(['adresseIp' => $ipAddress]);
            
            if ($imprimante) {
                return $imprimante;
            }
        }

        // Sinon chercher par numéro de série
        if ($numeroSerie) {
            $imprimante = $em->getRepository(Imprimante::class)
                ->findOneBy(['numeroSerie' => $numeroSerie]);
            
            if ($imprimante) {
                return $imprimante;
            }
        }

        // Créer une nouvelle imprimante
        $imprimante = new Imprimante();
        $imprimante->setSite($site);
        $imprimante->setModele($modele);
        $imprimante->setNumeroSerie($numeroSerie ?? 'N/A-' . uniqid());
        $imprimante->setAdresseIp($ipAddress);
        $imprimante->setEmplacement($emplacement);
        $imprimante->setSuivieParService(strtolower($managed) === 'true');
        
        $em->persist($imprimante);
        
        return $imprimante;
    }

    private function createReleveCompteur(Imprimante $imprimante, array $data): void
    {
        // READING_DATE = Date du relevé
        $dateReleve = $this->parseDate($data['READING_DATE'] ?? null);
        if (!$dateReleve) {
            return; // Pas de date = pas de relevé
        }

        $em = $this->getEntityManager();
        
        // Vérifier si un relevé existe déjà pour cette date
        $existing = $em->getRepository(ReleveCompteur::class)
            ->findOneBy([
                'imprimante' => $imprimante,
                'dateReleve' => $dateReleve
            ]);

        if ($existing) {
            return; // Ne pas dupliquer
        }

        $releve = new ReleveCompteur();
        $releve->setImprimante($imprimante);
        $releve->setDateReleve($dateReleve);
        // MONO_LIFE_COUNT = compteur noir
        $releve->setCompteurNoir($this->parseInt($data['MONO_LIFE_COUNT'] ?? null));
        // COLOR_LIFE_COUNT = compteur couleur
        $releve->setCompteurCouleur($this->parseInt($data['COLOR_LIFE_COUNT'] ?? null));
        // FAX_COUNT = compteur fax
        $releve->setCompteurFax($this->parseInt($data['FAX_COUNT'] ?? null));
        $releve->setSource('csv');

        $em->persist($releve);
    }

    private function createEtatConsommable(Imprimante $imprimante, array $data): void
    {
        // READING_DATE = Date du relevé (utilisée aussi pour la date de capture)
        $dateCapture = $this->parseDate($data['READING_DATE'] ?? null);
        if (!$dateCapture) {
            return;
        }

        $em = $this->getEntityManager();
        
        // Vérifier si un état existe déjà pour cette date
        $existing = $em->getRepository(EtatConsommable::class)
            ->findOneBy([
                'imprimante' => $imprimante,
                'dateCapture' => $dateCapture
            ]);

        if ($existing) {
            return; // Ne pas dupliquer
        }

        $etat = new EtatConsommable();
        $etat->setImprimante($imprimante);
        $etat->setDateCapture($dateCapture);
        
        // Toners : BLACK_LEVEL, CYAN_LEVEL, MAGENTA_LEVEL, YELLOW_LEVEL, WASTE_LEVEL
        $etat->setNoirPourcent($this->parsePourcent($data['BLACK_LEVEL'] ?? null));
        $etat->setCyanPourcent($this->parsePourcent($data['CYAN_LEVEL'] ?? null));
        $etat->setMagentaPourcent($this->parsePourcent($data['MAGENTA_LEVEL'] ?? null));
        $etat->setJaunePourcent($this->parsePourcent($data['YELLOW_LEVEL'] ?? null));
        $etat->setBacRecuperation($this->parsePourcent($data['WASTE_LEVEL'] ?? null));
        
        // Dates prévisionnelles d'épuisement
        $etat->setDateEpuisementNoir($this->parseDate($data['BLACK_DEPLETION_DATE'] ?? null));
        $etat->setDateEpuisementCyan($this->parseDate($data['CYAN_DEPLETION_DATE'] ?? null));
        $etat->setDateEpuisementMagenta($this->parseDate($data['MAGENTA_DEPLETION_DATE'] ?? null));
        $etat->setDateEpuisementJaune($this->parseDate($data['YELLOW_DEPLETION_DATE'] ?? null));

        $em->persist($etat);
    }

    private function parseDate(?string $dateStr): ?\DateTimeImmutable
    {
        if (empty($dateStr) || $dateStr === '01/01/0001') {
            return null;
        }

        try {
            // Format attendu : "09/04/2025 10:12" ou "09/04/2025"
            $date = \DateTimeImmutable::createFromFormat('d/m/Y H:i', $dateStr);
            if ($date === false) {
                $date = \DateTimeImmutable::createFromFormat('d/m/Y', $dateStr);
            }
            return $date ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseInt(?string $value): ?int
    {
        if (empty($value) || $value === '') {
            return null;
        }
        return (int) $value;
    }

    private function parsePourcent(?string $value): ?int
    {
        if (empty($value) || $value === '' || strtolower($value) === 'low') {
            return null;
        }

        // Enlever le % et convertir
        $value = str_replace('%', '', $value);
        $intValue = (int) $value;
        
        return $intValue > 0 ? $intValue : null;
    }
}

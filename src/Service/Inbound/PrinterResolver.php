<?php

declare(strict_types=1);

namespace App\Service\Inbound;

use App\Entity\Imprimante;
use App\Entity\InboundEvent;
use App\Entity\PrinterExternalRef;
use App\Repository\ImprimanteRepository;
use App\Repository\PrinterExternalRefRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Tente de retrouver une Imprimante à partir des meta (serial, deviceExternalId, ip, hostname).
 * Si trouvée, met à jour ou crée PrinterExternalRef pour le provider.
 */
final class PrinterResolver
{
    public function __construct(
        private readonly ImprimanteRepository $imprimanteRepository,
        private readonly PrinterExternalRefRepository $printerExternalRefRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @param array<string, mixed> $meta
     */
    public function resolveAndLink(InboundEvent $event, array $meta): ?Imprimante
    {
        $provider = $event->getProvider();
        $imprimante = null;

        $externalId = isset($meta['deviceExternalId']) && \is_string($meta['deviceExternalId'])
            ? trim($meta['deviceExternalId'])
            : null;
        $serial = isset($meta['serialNumber']) && \is_string($meta['serialNumber'])
            ? trim($meta['serialNumber'])
            : null;
        $ip = isset($meta['ipAddress']) && \is_string($meta['ipAddress'])
            ? trim($meta['ipAddress'])
            : null;

        if ($externalId !== null && $externalId !== '') {
            $ref = $this->printerExternalRefRepository->findByProviderAndExternalId($provider, $externalId);
            if ($ref !== null) {
                $imprimante = $ref->getImprimante();
            }
        }

        if ($imprimante === null && $serial !== null && $serial !== '') {
            $imprimante = $this->imprimanteRepository->findOneByNumeroSerie($serial);
        }

        if ($imprimante === null && $ip !== null && $ip !== '') {
            $imprimante = $this->imprimanteRepository->findOneByAdresseIp($ip);
        }

        if ($imprimante === null) {
            return null;
        }

        $event->setImprimante($imprimante);

        if ($externalId !== null && $externalId !== '') {
            $this->upsertExternalRef($imprimante, $provider, $externalId);
        }

        return $imprimante;
    }

    private function upsertExternalRef(Imprimante $imprimante, string $provider, string $externalId): void
    {
        $ref = $this->printerExternalRefRepository->findByProviderAndExternalId($provider, $externalId);
        $now = new \DateTimeImmutable();
        if ($ref !== null) {
            $ref->setLastSeenAt($now);
            if ($ref->getImprimante()->getId() !== $imprimante->getId()) {
                $ref->setImprimante($imprimante);
            }
        } else {
            $ref = new PrinterExternalRef();
            $ref->setImprimante($imprimante);
            $ref->setProvider($provider);
            $ref->setExternalId($externalId);
            $ref->setLastSeenAt($now);
            $this->em->persist($ref);
        }
    }
}

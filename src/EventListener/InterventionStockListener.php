<?php

namespace App\EventListener;

use App\Entity\Intervention;
use App\Enum\StatutIntervention;
use App\Service\InterventionClotureStockService;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;

/**
 * Quand une intervention livraison toner passe au statut Terminée,
 * applique les mouvements de stock (débit entreprise, crédit site).
 */
class InterventionStockListener implements EventSubscriber
{
    public function __construct(
        private InterventionClotureStockService $interventionClotureStockService
    ) {
    }

    public function getSubscribedEvents(): array
    {
        return [Events::postUpdate];
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Intervention) {
            return;
        }

        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();
        $changeSet = $uow->getEntityChangeSet($entity);

        if (!isset($changeSet['statut'])) {
            return;
        }

        [$oldStatut, $newStatut] = $changeSet['statut'];
        if ($oldStatut === StatutIntervention::TERMINEE || $newStatut !== StatutIntervention::TERMINEE) {
            return;
        }

        $entity->setDateIntervention($entity->getDateCreation());
        $this->interventionClotureStockService->applyStockLivraison($entity);
    }
}

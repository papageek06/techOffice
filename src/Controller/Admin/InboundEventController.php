<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\InboundEvent;
use App\Repository\InboundEventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/inbound-events')]
#[IsGranted('ROLE_ADMIN')]
final class InboundEventController extends AbstractController
{
    public function __construct(
        private readonly InboundEventRepository $inboundEventRepository,
    ) {
    }

    #[Route(name: 'app_admin_inbound_events_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $status = $request->query->get('status');
        $provider = $request->query->get('provider');
        $events = $this->inboundEventRepository->findByStatusAndProvider($status, $provider, 200);

        if ($request->getRequestFormat() === 'json' || $request->query->get('format') === 'json') {
            $data = array_map(fn (InboundEvent $e) => [
                'id' => $e->getId(),
                'receivedAt' => $e->getReceivedAt()->format(\DateTimeInterface::ATOM),
                'status' => $e->getStatus(),
                'provider' => $e->getProvider(),
                'endpoint' => $e->getEndpoint(),
                'meta' => $e->getMeta(),
            ], $events);
            return $this->json($data);
        }

        return $this->render('admin/inbound_events/index.html.twig', [
            'events' => $events,
            'status_filter' => $status,
            'provider_filter' => $provider,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_inbound_events_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Request $request, int $id): Response
    {
        $event = $this->inboundEventRepository->find($id);
        if ($event === null) {
            throw new NotFoundHttpException();
        }

        if ($request->getRequestFormat() === 'json' || $request->query->get('format') === 'json') {
            return $this->json([
                'id' => $event->getId(),
                'provider' => $event->getProvider(),
                'endpoint' => $event->getEndpoint(),
                'receivedAt' => $event->getReceivedAt()->format(\DateTimeInterface::ATOM),
                'contentType' => $event->getContentType(),
                'status' => $event->getStatus(),
                'headers' => $event->getHeaders(),
                'meta' => $event->getMeta(),
                'parseError' => $event->getParseError(),
                'payloadRaw' => $event->getPayloadRaw(),
                'payloadJson' => $event->getPayloadJson(),
                'imprimanteId' => $event->getImprimante()?->getId(),
            ]);
        }

        return $this->render('admin/inbound_events/show.html.twig', [
            'event' => $event,
        ]);
    }
}

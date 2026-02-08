<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\InboundEvent;
use App\Message\ProcessInboundEventMessage;
use App\Repository\InboundEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

final class InboundWebhookController extends AbstractController
{
    private const PROVIDER = InboundEvent::PROVIDER_PRINTAUDIT_FM;
    private const ENDPOINT = 'webhook';

    public function __construct(
        private readonly string $webhookToken,
        private readonly InboundEventRepository $inboundEventRepository,
        private readonly EntityManagerInterface $em,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    #[Route('/api/inbound/printaudit/webhook', name: 'api_inbound_printaudit_webhook', methods: ['POST'])]
    public function printauditWebhook(Request $request): JsonResponse
    {
        if (!$this->isTokenValid($request)) {
            return new JsonResponse(['ok' => false, 'error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $rawBody = $request->getContent();
        $contentType = $request->headers->get('Content-Type');
        $headers = $this->normalizeHeaders($request->headers->all());

        $fingerprint = hash('sha256', self::PROVIDER . $rawBody);
        $existing = $this->inboundEventRepository->findByFingerprint($fingerprint);
        if ($existing !== null) {
            return new JsonResponse([
                'ok' => true,
                'id' => $existing->getId(),
                'duplicate' => true,
            ]);
        }

        $event = new InboundEvent();
        $event->setProvider(self::PROVIDER);
        $event->setEndpoint(self::ENDPOINT);
        $event->setReceivedAt(new \DateTimeImmutable());
        $event->setContentType($contentType);
        $event->setHeaders($headers);
        $event->setPayloadRaw($rawBody);
        $event->setPayloadJson($this->tryDecodeJson($rawBody));
        $event->setStatus(InboundEvent::STATUS_RECEIVED);
        $event->setFingerprint($fingerprint);

        $this->em->persist($event);
        $this->em->flush();

        $this->messageBus->dispatch(new ProcessInboundEventMessage($event->getId()));

        return new JsonResponse([
            'ok' => true,
            'id' => $event->getId(),
        ]);
    }

    private function isTokenValid(Request $request): bool
    {
        if ($this->webhookToken === '') {
            return false;
        }
        $headerToken = $request->headers->get('X-Webhook-Token');
        if ($headerToken !== null && $headerToken !== '') {
            return hash_equals($this->webhookToken, $headerToken);
        }
        $auth = $request->headers->get('Authorization');
        if ($auth !== null && stripos($auth, 'Bearer ') === 0) {
            return hash_equals($this->webhookToken, trim(substr($auth, 7)));
        }
        return false;
    }

    /**
     * @param array<string, array<int, string>> $headers
     * @return array<string, string|string[]>
     */
    private function normalizeHeaders(array $headers): array
    {
        $out = [];
        foreach ($headers as $name => $values) {
            $out[$name] = \is_array($values) && count($values) === 1 ? $values[0] : $values;
        }
        return $out;
    }

    /** @return array<string, mixed>|null */
    private function tryDecodeJson(string $raw): ?array
    {
        $decoded = json_decode($raw, true);
        return \is_array($decoded) ? $decoded : null;
    }
}

<?php

declare(strict_types=1);

namespace App\Service\M365;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Microsoft Graph API client.
 * - Pagination via @odata.nextLink
 * - Simple retry on 429 (rate limit)
 */
class MicrosoftGraphClient
{
    private const BASE_URL = 'https://graph.microsoft.com/v1.0';
    private const MAX_RETRIES = 3;
    private const RETRY_AFTER_DEFAULT = 2;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly ?LoggerInterface $logger = null
    ) {
    }

    /**
     * GET /me/contactFolders
     * @return list<array<string, mixed>>
     */
    public function getContactFolders(string $accessToken): array
    {
        $url = self::BASE_URL . '/me/contactFolders';
        $all = [];
        do {
            $response = $this->request('GET', $url, $accessToken);
            $data = $response->toArray();
            $value = $data['value'] ?? [];
            foreach ($value as $folder) {
                $all[] = $folder;
            }
            $url = $data['@odata.nextLink'] ?? null;
        } while ($url !== null);
        return $all;
    }

    /**
     * GET /me/contactFolders/{folderId}/contacts
     * Optional filter: $filter=lastModifiedDateTime ge {ISO8601} (incremental sync).
     *
     * @return \Generator<array<string, mixed>>
     */
    public function getContacts(string $accessToken, string $folderId, ?\DateTimeImmutable $modifiedSince = null): \Generator
    {
        $params = ['$top' => 100];
        if ($modifiedSince !== null) {
            $params['$filter'] = 'lastModifiedDateTime ge ' . $modifiedSince->format('Y-m-d\TH:i:s\Z');
        }
        $url = self::BASE_URL . '/me/contactFolders/' . $folderId . '/contacts?' . http_build_query($params);

        do {
            $response = $this->request('GET', $url, $accessToken);
            $data = $response->toArray();
            $value = $data['value'] ?? [];
            foreach ($value as $contact) {
                yield $contact;
            }
            $url = $data['@odata.nextLink'] ?? null;
        } while ($url !== null);
    }

    private function request(string $method, string $url, string $accessToken): \Symfony\Contracts\HttpClient\ResponseInterface
    {
        $retries = 0;
        while (true) {
            try {
                $response = $this->httpClient->request($method, $url, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Content-Type' => 'application/json',
                    ],
                ]);
                $status = $response->getStatusCode();
                if ($status === 429 && $retries < self::MAX_RETRIES) {
                    $retryAfter = (int) ($response->getHeaders(false)['retry-after'][0] ?? self::RETRY_AFTER_DEFAULT);
                    $this->logger?->warning('M365 Graph: rate limited, retry after {seconds}s', ['seconds' => $retryAfter]);
                    sleep($retryAfter);
                    $retries++;
                    continue;
                }
                $response->getStatusCode(); // trigger on 4xx/5xx to throw
                return $response;
            } catch (\Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface $e) {
                $this->logger?->error('M365 Graph request failed', ['url' => $url, 'error' => $e->getMessage()]);
                throw $e;
            }
        }
    }
}

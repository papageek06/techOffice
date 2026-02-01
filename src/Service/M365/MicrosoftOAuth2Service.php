<?php

declare(strict_types=1);

namespace App\Service\M365;

use App\Entity\OAuthToken;
use App\Entity\User;
use App\Repository\OAuthTokenRepository;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * OAuth2 Authorization Code flow for Microsoft 365.
 * Exchanges code for tokens, refreshes access token when expired.
 */
class MicrosoftOAuth2Service
{
    private const PROVIDER = 'm365';
    private const AUTHORIZE_URL = 'https://login.microsoftonline.com/%s/oauth2/v2.0/authorize';
    private const TOKEN_URL = 'https://login.microsoftonline.com/%s/oauth2/v2.0/token';
    private const SCOPES = 'openid https://graph.microsoft.com/Contacts.Read.Shared offline_access';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly OAuthTokenRepository $oauthTokenRepository,
        private readonly string $tenantId,
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $redirectUri,
        private readonly ?LoggerInterface $logger = null
    ) {
    }

    public function getAuthorizationUrl(string $state): string
    {
        $params = http_build_query([
            'client_id' => $this->clientId,
            'response_type' => 'code',
            'redirect_uri' => $this->redirectUri,
            'response_mode' => 'query',
            'scope' => self::SCOPES,
            'state' => $state,
            'prompt' => 'consent',
        ], '', '&', \PHP_QUERY_RFC3986);

        return sprintf(self::AUTHORIZE_URL, $this->tenantId) . '?' . $params;
    }

    public function getTokenFromCode(User $user, string $code): OAuthToken
    {
        $response = $this->httpClient->request('POST', sprintf(self::TOKEN_URL, $this->tenantId), [
            'body' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code' => $code,
                'redirect_uri' => $this->redirectUri,
                'grant_type' => 'authorization_code',
            ],
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
        ]);

        $data = $response->toArray();
        $token = $this->oauthTokenRepository->findForUserAndProvider($user, self::PROVIDER);
        if ($token === null) {
            $token = new OAuthToken();
            $token->setUser($user);
            $token->setProvider(self::PROVIDER);
        }

        $token->setAccessToken($data['access_token']);
        $token->setRefreshToken($data['refresh_token'] ?? $token->getRefreshToken());
        $expiresIn = (int) ($data['expires_in'] ?? 3600);
        $token->setExpiresAt(new \DateTimeImmutable('+' . $expiresIn . ' seconds'));
        $this->oauthTokenRepository->save($token, true);

        $this->logger?->info('M365 OAuth2: token obtained for user', ['user_id' => $user->getId()]);
        return $token;
    }

    public function refreshToken(OAuthToken $token): void
    {
        if ($token->getRefreshToken() === null) {
            throw new \RuntimeException('No refresh token available for M365.');
        }

        $response = $this->httpClient->request('POST', sprintf(self::TOKEN_URL, $this->tenantId), [
            'body' => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $token->getRefreshToken(),
                'grant_type' => 'refresh_token',
            ],
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
        ]);

        $data = $response->toArray();
        $token->setAccessToken($data['access_token']);
        if (isset($data['refresh_token'])) {
            $token->setRefreshToken($data['refresh_token']);
        }
        $expiresIn = (int) ($data['expires_in'] ?? 3600);
        $token->setExpiresAt(new \DateTimeImmutable('+' . $expiresIn . ' seconds'));
        $this->oauthTokenRepository->save($token, true);

        $this->logger?->info('M365 OAuth2: token refreshed', ['token_id' => $token->getId()]);
    }

    public function getValidAccessToken(User $user): ?string
    {
        $token = $this->oauthTokenRepository->findForUserAndProvider($user, self::PROVIDER);
        if ($token === null) {
            return null;
        }
        if ($token->isExpiredOrExpiringSoon()) {
            $this->refreshToken($token);
        }
        return $token->getAccessToken();
    }
}

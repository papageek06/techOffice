<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class InboundApiControllerTest extends WebTestCase
{
    private const VALID_TOKEN = 'test-inbound-token-12345';

    public function testReportWithoutTokenReturns401(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/inbound/printaudit/report', [], [], [
            'CONTENT_TYPE' => 'multipart/form-data',
        ]);
        self::assertResponseStatusCodeSame(401);
        self::assertResponseHeaderSame('Content-Type', 'application/json');
    }

    public function testReportWithWrongTokenReturns401(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/inbound/printaudit/report', [], [], [
            'HTTP_X-Inbound-Token' => 'wrong-token',
        ]);
        self::assertResponseStatusCodeSame(401);
    }

    public function testAlertWithoutTokenReturns401(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/inbound/mail/alert', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], '{}');
        self::assertResponseStatusCodeSame(401);
    }

    public function testAlertWithWrongTokenReturns401(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/inbound/mail/alert', [], [], [
            'HTTP_X-Inbound-Token' => 'wrong-token',
            'CONTENT_TYPE' => 'application/json',
        ], '{"subject":"Test","from":"a@b.com","body":"x"}');
        self::assertResponseStatusCodeSame(401);
    }

    public function testAlertWithValidTokenAndInvalidPayloadReturns400(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/inbound/mail/alert', [], [], [
            'HTTP_X-Inbound-Token' => self::VALID_TOKEN,
            'CONTENT_TYPE' => 'application/json',
        ], 'not json');
        self::assertResponseStatusCodeSame(400);
    }

    public function testAlertWithValidTokenAndValidPayloadReturns200(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/inbound/mail/alert', [], [], [
            'HTTP_X-Inbound-Token' => self::VALID_TOKEN,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'subject' => 'Test alert',
            'from' => 'test@example.com',
            'body' => 'Body text',
            'severity' => 'info',
        ], \JSON_THROW_ON_ERROR));
        self::assertResponseIsSuccessful();
        $json = json_decode($client->getResponse()->getContent(), true);
        self::assertIsArray($json);
        self::assertTrue($json['ok'] ?? false);
        self::assertArrayHasKey('alertId', $json);
        self::assertSame(0, $json['attachmentsSaved'] ?? -1);
    }
}

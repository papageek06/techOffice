<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\InboundFileStorageService;
use PHPUnit\Framework\TestCase;

class InboundFileStorageServiceTest extends TestCase
{
    public function testParseSizeToBytes(): void
    {
        self::assertSame(1024, InboundFileStorageService::parseSizeToBytes('1024'));
        self::assertSame(1024, InboundFileStorageService::parseSizeToBytes('1K'));
        self::assertSame(2048, InboundFileStorageService::parseSizeToBytes('2K'));
        self::assertSame(1024 * 1024, InboundFileStorageService::parseSizeToBytes('1M'));
        self::assertSame(10 * 1024 * 1024, InboundFileStorageService::parseSizeToBytes('10M'));
        self::assertSame(1024 * 1024 * 1024, InboundFileStorageService::parseSizeToBytes('1G'));
    }
}

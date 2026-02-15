<?php

declare(strict_types=1);

namespace App\Message;

final class ProcessInboundAlertMessage
{
    public function __construct(
        private readonly int $alertId,
    ) {
    }

    public function getAlertId(): int
    {
        return $this->alertId;
    }
}

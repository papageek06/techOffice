<?php

declare(strict_types=1);

namespace App\Message;

class ProcessInboundEventMessage
{
    public function __construct(
        private readonly int $inboundEventId
    ) {
    }

    public function getInboundEventId(): int
    {
        return $this->inboundEventId;
    }
}

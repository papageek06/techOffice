<?php

namespace App\Dto;

class EmailAttachmentDto
{
    public function __construct(
        public readonly string $originalName,
        public readonly ?string $mimeType,
        public readonly string $content,
        public readonly int $size
    ) {
    }
}

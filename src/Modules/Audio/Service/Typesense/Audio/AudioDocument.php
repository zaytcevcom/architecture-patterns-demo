<?php

declare(strict_types=1);

namespace App\Modules\Audio\Service\Typesense\Audio;

readonly class AudioDocument
{
    public function __construct(
        public int $identifier,
        public array $unionIds,
        public string $title,
        public array $artists,
        public int $year,
    ) {}
}

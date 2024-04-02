<?php

declare(strict_types=1);

namespace App\Modules\Audio\Service\Typesense\Audio;

class AudioQuery
{
    public function __construct(
        public string $search,
        public ?int $unionId = null,
        public ?bool $isSingle = null,
        public int $limit = 150
    ) {}
}

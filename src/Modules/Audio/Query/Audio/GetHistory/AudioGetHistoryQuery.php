<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\Audio\GetHistory;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AudioGetHistoryQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        public string $search = '',
        public int $count = 100,
        public int $offset = 0,
    ) {}
}

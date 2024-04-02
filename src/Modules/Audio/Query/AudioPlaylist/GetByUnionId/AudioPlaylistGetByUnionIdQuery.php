<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\AudioPlaylist\GetByUnionId;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AudioPlaylistGetByUnionIdQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $unionId,
        public string $search = '',
        public int $sort = 0,
        public int $count = 100,
        public int $offset = 0,
    ) {}
}

<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\AudioAlbum\GetByUnionId;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AudioAlbumGetByUnionIdQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $unionId,
        public string $search = '',
        public ?string $filter = null,
        public int $sort = 0,
        public int $count = 100,
        public int $offset = 0,
    ) {}
}

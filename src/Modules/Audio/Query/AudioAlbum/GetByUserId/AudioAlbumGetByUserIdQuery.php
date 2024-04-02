<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\AudioAlbum\GetByUserId;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AudioAlbumGetByUserIdQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        public string $search = '',
        public int $sort = 0,
        public int $count = 100,
        public int $offset = 0,
    ) {}
}

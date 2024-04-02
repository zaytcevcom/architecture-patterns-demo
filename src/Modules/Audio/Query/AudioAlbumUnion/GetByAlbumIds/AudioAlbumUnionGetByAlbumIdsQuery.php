<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\AudioAlbumUnion\GetByAlbumIds;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AudioAlbumUnionGetByAlbumIdsQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public array $albumIds,
    ) {}
}

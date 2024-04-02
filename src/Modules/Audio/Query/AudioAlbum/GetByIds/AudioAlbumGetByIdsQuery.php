<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\AudioAlbum\GetByIds;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AudioAlbumGetByIdsQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public array $ids
    ) {}
}

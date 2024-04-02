<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\Audio\GetByAudioAlbumId;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AudioGetByAudioAlbumIdQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $albumId,
        public int $sort = 0,
        public int $count = 100,
        public int $offset = 0,
    ) {}
}

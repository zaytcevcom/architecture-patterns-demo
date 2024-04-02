<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\Audio\GetByAudioPlaylistId;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AudioGetByAudioPlaylistIdQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $playlistId,
        public int $sort = 0,
        public int $count = 100,
        public int $offset = 0,
    ) {}
}

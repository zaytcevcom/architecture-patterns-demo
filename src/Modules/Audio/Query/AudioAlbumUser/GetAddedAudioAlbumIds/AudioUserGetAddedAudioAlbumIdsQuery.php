<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\AudioAlbumUser\GetAddedAudioAlbumIds;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AudioUserGetAddedAudioAlbumIdsQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public array $ids,
    ) {}
}

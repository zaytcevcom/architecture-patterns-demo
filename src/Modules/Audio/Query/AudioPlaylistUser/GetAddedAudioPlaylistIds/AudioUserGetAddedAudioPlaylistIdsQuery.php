<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\AudioPlaylistUser\GetAddedAudioPlaylistIds;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AudioUserGetAddedAudioPlaylistIdsQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public array $ids,
    ) {}
}

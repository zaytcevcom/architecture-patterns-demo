<?php

declare(strict_types=1);

namespace App\Modules\Audio\Command\AudioPlaylist\Add;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AudioPlaylistAddCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $playlistId,
    ) {}
}

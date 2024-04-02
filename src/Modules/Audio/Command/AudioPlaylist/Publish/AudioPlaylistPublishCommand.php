<?php

declare(strict_types=1);

namespace App\Modules\Audio\Command\AudioPlaylist\Publish;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AudioPlaylistPublishCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $playlistId,
        #[Assert\NotBlank]
        public int $time,
    ) {}
}

<?php

declare(strict_types=1);

namespace App\Modules\Audio\Command\AudioPlaylist\UnPublish;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AudioPlaylistUnPublishCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $playlistId,
    ) {}
}

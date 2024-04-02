<?php

declare(strict_types=1);

namespace App\Modules\Audio\Command\AudioPlaylist\Create;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AudioPlaylistCreateCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $unionId,
        #[Assert\NotBlank]
        public string $name,
        #[Assert\NotBlank]
        public string $artists,
    ) {}
}

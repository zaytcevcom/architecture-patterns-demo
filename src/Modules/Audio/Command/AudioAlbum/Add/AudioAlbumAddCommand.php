<?php

declare(strict_types=1);

namespace App\Modules\Audio\Command\AudioAlbum\Add;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AudioAlbumAddCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $albumId,
    ) {}
}

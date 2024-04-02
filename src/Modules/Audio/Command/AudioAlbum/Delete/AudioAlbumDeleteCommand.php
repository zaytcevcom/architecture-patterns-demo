<?php

declare(strict_types=1);

namespace App\Modules\Audio\Command\AudioAlbum\Delete;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AudioAlbumDeleteCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $albumId,
    ) {}
}

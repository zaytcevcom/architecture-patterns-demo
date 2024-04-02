<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\AudioPlaylist\GetById;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AudioPlaylistGetByIdQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $id
    ) {}
}

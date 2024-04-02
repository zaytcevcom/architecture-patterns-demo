<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\PhotoSave;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PhotoSaveCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public string $host,
        #[Assert\NotBlank]
        public string $fileId,
    ) {}
}

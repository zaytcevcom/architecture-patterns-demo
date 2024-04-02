<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\CreateSystemData;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UnionCreateSystemDataCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $unionId,
        public ?string $photoHost,
        public ?string $photoFileId,
    ) {}
}

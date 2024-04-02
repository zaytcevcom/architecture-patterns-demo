<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Leave;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UnionLeaveCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $unionId
    ) {}
}

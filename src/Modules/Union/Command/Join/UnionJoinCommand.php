<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Join;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UnionJoinCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $unionId
    ) {}
}

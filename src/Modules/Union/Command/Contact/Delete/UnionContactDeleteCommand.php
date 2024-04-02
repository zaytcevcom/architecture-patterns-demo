<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Contact\Delete;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UnionContactDeleteCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $unionId,
        #[Assert\NotBlank]
        public int $contactId,
    ) {}
}

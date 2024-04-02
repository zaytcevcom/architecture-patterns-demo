<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Contact\Create;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UnionContactCreateCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $sourceId,
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $unionId,
        public ?string $position,
        public ?string $phone,
        public ?string $email,
    ) {}
}

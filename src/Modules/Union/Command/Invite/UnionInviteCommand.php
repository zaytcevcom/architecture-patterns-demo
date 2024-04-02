<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Invite;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UnionInviteCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $sourceId,
        #[Assert\NotBlank]
        public int $targetId,
        #[Assert\NotBlank]
        public int $unionId
    ) {}
}

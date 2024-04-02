<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Community\Management\UpdatePrivacy;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CommunityUpdatePrivacyCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $unionId,
        public int $kind,
        public bool $membersHide,
    ) {}
}

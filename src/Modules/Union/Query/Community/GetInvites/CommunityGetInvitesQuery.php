<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Community\GetInvites;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CommunityGetInvitesQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $unionId,
        #[Assert\NotBlank]
        public int $userId,
        public ?string $search,
        public int $count = 100,
        public int $offset = 0,
    ) {}
}

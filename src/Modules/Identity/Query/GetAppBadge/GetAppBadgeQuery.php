<?php

declare(strict_types=1);

namespace App\Modules\Identity\Query\GetAppBadge;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class GetAppBadgeQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
    ) {}
}

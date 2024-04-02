<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Notification\IsNotificationSubscribe;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UnionIsNotificationSubscribeQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $unionId
    ) {}
}

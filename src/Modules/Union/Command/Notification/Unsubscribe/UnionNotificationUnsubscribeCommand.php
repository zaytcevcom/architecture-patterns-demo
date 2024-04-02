<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Notification\Unsubscribe;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UnionNotificationUnsubscribeCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $unionId
    ) {}
}

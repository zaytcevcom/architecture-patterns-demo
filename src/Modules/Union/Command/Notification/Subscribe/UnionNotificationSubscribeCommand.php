<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Notification\Subscribe;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UnionNotificationSubscribeCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $unionId
    ) {}
}

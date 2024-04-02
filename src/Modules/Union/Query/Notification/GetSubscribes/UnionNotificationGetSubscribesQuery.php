<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Notification\GetSubscribes;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UnionNotificationGetSubscribesQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $unionId
    ) {}
}

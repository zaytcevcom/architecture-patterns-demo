<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Message\MessageNew;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class MessageNewCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $messageId,
    ) {}
}

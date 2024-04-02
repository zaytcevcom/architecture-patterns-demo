<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Contact\ContactAdded;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ContactAddedCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $targetId,
    ) {}
}

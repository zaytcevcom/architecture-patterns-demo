<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Flow\FlowReposted;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class FlowRepostedCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $flowId,
    ) {}
}

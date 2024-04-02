<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Link\Create;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UnionLinkCreateCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $unionId,
        #[Assert\NotBlank]
        public string $url,
        #[Assert\NotBlank]
        public string $title,
    ) {}
}

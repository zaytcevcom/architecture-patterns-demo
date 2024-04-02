<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Link\Update;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UnionLinkUpdateCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $unionId,
        #[Assert\NotBlank]
        public int $linkId,
        #[Assert\NotBlank]
        public string $url,
        #[Assert\NotBlank]
        public string $title,
    ) {}
}

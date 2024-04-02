<?php

declare(strict_types=1);

namespace App\Modules\Identity\Query\GetByIds;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class IdentityGetByIdsQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public array|string $ids,
        public array|string $fields = '',
    ) {}
}

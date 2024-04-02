<?php

declare(strict_types=1);

namespace App\Modules\Identity\Query\GetById;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class IdentityGetByIdQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public ?int $id = null,
        public array|string $fields = '',
    ) {}
}

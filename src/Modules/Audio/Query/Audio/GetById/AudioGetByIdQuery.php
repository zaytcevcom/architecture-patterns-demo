<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\Audio\GetById;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AudioGetByIdQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $id
    ) {}
}

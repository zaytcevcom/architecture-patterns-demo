<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\Audio\GetByIds;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AudioGetByIdsQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public array $ids
    ) {}
}

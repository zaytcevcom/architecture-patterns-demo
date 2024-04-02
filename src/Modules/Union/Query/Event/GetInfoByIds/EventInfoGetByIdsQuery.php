<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Event\GetInfoByIds;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class EventInfoGetByIdsQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public array $ids,
    ) {}
}

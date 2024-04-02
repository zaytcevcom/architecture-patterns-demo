<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Event\GetInfoByUnionIds;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class EventInfoGetByUnionIdsQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public array $ids,
    ) {}
}

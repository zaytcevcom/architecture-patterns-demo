<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Event\GetInfosByPlaceIds;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class EventInfoGetByPlaceIdsQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public array $ids,
    ) {}
}

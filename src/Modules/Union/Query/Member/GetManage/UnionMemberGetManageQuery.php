<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Member\GetManage;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UnionMemberGetManageQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $unionId,
        public ?string $search,
        public int $count = 100,
        public int $offset = 0,
    ) {}
}

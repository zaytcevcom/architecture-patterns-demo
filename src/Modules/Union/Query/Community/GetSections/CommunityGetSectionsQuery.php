<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Community\GetSections;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CommunityGetSectionsQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $unionId
    ) {}
}

<?php

declare(strict_types=1);

namespace App\Modules\Union\Service\Typesense\Community;

readonly class CommunityDocument
{
    public function __construct(
        public int $identifier,
        public string $title,
        public ?int $sphereId,
        public ?int $categoryId,
        public ?int $categoryKind,
        public ?int $cityId,
        public int $countMembers,
    ) {}
}

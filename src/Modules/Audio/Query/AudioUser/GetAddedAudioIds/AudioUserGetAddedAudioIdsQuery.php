<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\AudioUser\GetAddedAudioIds;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AudioUserGetAddedAudioIdsQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public array $ids
    ) {}
}

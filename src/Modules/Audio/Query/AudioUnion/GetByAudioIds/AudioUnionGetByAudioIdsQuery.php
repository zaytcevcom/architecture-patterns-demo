<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\AudioUnion\GetByAudioIds;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AudioUnionGetByAudioIdsQuery
{
    public function __construct(
        #[Assert\NotBlank]
        public array $audioIds,
    ) {}
}

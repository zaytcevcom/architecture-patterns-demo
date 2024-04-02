<?php

declare(strict_types=1);

namespace App\Modules\Audio\Command\SaveHlsInfo;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AudioSaveHlsInfoCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public string $fileId,
        #[Assert\NotBlank]
        public string $url,
    ) {}
}

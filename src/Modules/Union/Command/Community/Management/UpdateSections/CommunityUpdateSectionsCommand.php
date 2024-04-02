<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Community\Management\UpdateSections;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CommunityUpdateSectionsCommand
{
    public function __construct(
        #[Assert\NotBlank]
        public int $userId,
        #[Assert\NotBlank]
        public int $unionId,
        public bool $posts,
        public bool $photos,
        public bool $videos,
        public bool $audios,
        public bool $contacts,
        public bool $links,
        public bool $messages,
    ) {}
}

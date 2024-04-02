<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Link\Update;

use App\Modules\Union\Entity\UnionLink\UnionLinkRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class UnionLinkUpdateHandler
{
    public function __construct(
        private UnionLinkRepository $unionLinkRepository,
        private Flusher $flusher,
    ) {}

    public function handle(UnionLinkUpdateCommand $command): void
    {
        $link = $this->unionLinkRepository->getById($command->linkId);

        // todo: permissions

        $link->edit(
            url: $command->url,
            title: $command->title,
        );

        $this->unionLinkRepository->add($link);

        $this->flusher->flush();
    }
}

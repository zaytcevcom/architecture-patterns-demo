<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Link\Delete;

use App\Modules\Union\Command\UpdateCounter\UnionUpdateCounterLinksHandler;
use App\Modules\Union\Entity\UnionLink\UnionLinkRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class UnionLinkDeleteHandler
{
    public function __construct(
        private UnionLinkRepository $unionLinkRepository,
        private UnionUpdateCounterLinksHandler $unionUpdateCounterLinksHandler,
        private Flusher $flusher
    ) {}

    public function handle(UnionLinkDeleteCommand $command): void
    {
        $contact = $this->unionLinkRepository->getById($command->linkId);

        // todo: permissions

        $contact->markDeleted();

        $this->flusher->flush();

        $this->unionUpdateCounterLinksHandler->handle($contact->getUnionId());
    }
}

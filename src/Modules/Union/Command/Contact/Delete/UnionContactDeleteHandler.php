<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Contact\Delete;

use App\Modules\Union\Command\UpdateCounter\UnionUpdateCounterContactsHandler;
use App\Modules\Union\Entity\UnionContact\UnionContactRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class UnionContactDeleteHandler
{
    public function __construct(
        private UnionContactRepository $unionContactRepository,
        private UnionUpdateCounterContactsHandler $unionUpdateCounterContactsHandler,
        private Flusher $flusher
    ) {}

    public function handle(UnionContactDeleteCommand $command): void
    {
        $contact = $this->unionContactRepository->getById($command->contactId);

        // todo: permissions

        $contact->markDeleted();

        $this->flusher->flush();

        $this->unionUpdateCounterContactsHandler->handle($contact->getUnionId());
    }
}

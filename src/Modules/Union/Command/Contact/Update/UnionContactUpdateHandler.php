<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Contact\Update;

use App\Modules\Union\Entity\UnionContact\UnionContactRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class UnionContactUpdateHandler
{
    public function __construct(
        private UnionContactRepository $unionContactRepository,
        private Flusher $flusher,
    ) {}

    public function handle(UnionContactUpdateCommand $command): void
    {
        $contact = $this->unionContactRepository->getById($command->contactId);

        // todo: permissions

        $contact->edit(
            userId: $command->userId,
            position: $command->position,
            email: $command->email,
            phone: $command->phone,
        );

        $this->unionContactRepository->add($contact);

        $this->flusher->flush();
    }
}

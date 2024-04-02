<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Event\Management\Update;

use App\Http\Action\Unifier\Union\UnionUnifier;
use App\Modules\Union\Entity\Union\UnionRepository;
use App\Modules\Union\Event\Event\EventEventPublisher;
use App\Modules\Union\Event\Event\EventQueue;
use App\Modules\Union\Service\UnionRealtimeNotifier;
use ZayMedia\Shared\Components\Flusher;

final readonly class EventUpdateHandler
{
    public function __construct(
        private UnionRepository $unionRepository,
        private UnionRealtimeNotifier $unionRealtimeNotifier,
        private UnionUnifier $unionUnifier,
        private Flusher $flusher,
        private EventEventPublisher $eventPublisher,
    ) {}

    public function handle(EventUpdateCommand $command): void
    {
        $event = $this->unionRepository->getById($command->unionId);

        $event->edit(
            name: $command->name,
            description: $command->description,
            categoryId: $command->categoryId,
            website: $command->website,
            status: $command->status,
        );

        $this->flusher->flush();

        $this->unionRealtimeNotifier->update(
            unionId: $event->getId(),
            data: $this->unionUnifier->unifyOne(null, $event->toArray())
        );

        $this->eventPublisher->handle(EventQueue::UPDATED, $event->getId());
    }
}

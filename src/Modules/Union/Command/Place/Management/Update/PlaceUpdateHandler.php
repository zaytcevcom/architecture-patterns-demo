<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Place\Management\Update;

use App\Http\Action\Unifier\Union\UnionUnifier;
use App\Modules\Union\Entity\Union\UnionRepository;
use App\Modules\Union\Event\Place\PlaceEventPublisher;
use App\Modules\Union\Event\Place\PlaceQueue;
use App\Modules\Union\Service\UnionRealtimeNotifier;
use ZayMedia\Shared\Components\Flusher;

final readonly class PlaceUpdateHandler
{
    public function __construct(
        private UnionRepository $unionRepository,
        private UnionRealtimeNotifier $unionRealtimeNotifier,
        private UnionUnifier $unionUnifier,
        private Flusher $flusher,
        private PlaceEventPublisher $eventPublisher,
    ) {}

    public function handle(PlaceUpdateCommand $command): void
    {
        $place = $this->unionRepository->getById($command->unionId);

        $place->edit(
            name: $command->name,
            description: $command->description,
            categoryId: $command->categoryId,
            website: $command->website,
            status: $command->status,
        );

        $this->flusher->flush();

        $this->unionRealtimeNotifier->update(
            unionId: $place->getId(),
            data: $this->unionUnifier->unifyOne(null, $place->toArray())
        );

        $this->eventPublisher->handle(PlaceQueue::UPDATED, $place->getId());
    }
}

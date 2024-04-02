<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Place\Management\UpdateAgeLimit;

use App\Http\Action\Unifier\Union\UnionUnifier;
use App\Modules\Union\Entity\Union\UnionRepository;
use App\Modules\Union\Event\Place\PlaceEventPublisher;
use App\Modules\Union\Event\Place\PlaceQueue;
use App\Modules\Union\Service\UnionRealtimeNotifier;
use ZayMedia\Shared\Components\Flusher;

final readonly class PlaceUpdateAgeLimitHandler
{
    public function __construct(
        private UnionRepository $unionRepository,
        private UnionRealtimeNotifier $unionRealtimeNotifier,
        private UnionUnifier $unionUnifier,
        private Flusher $flusher,
        private PlaceEventPublisher $eventPublisher,
    ) {}

    public function handle(PlaceUpdateAgeLimitCommand $command): void
    {
        $place = $this->unionRepository->getById($command->unionId);

        $place->setAgeLimit(
            ageLimit: $command->ageLimit
        );

        $this->flusher->flush();

        $this->unionRealtimeNotifier->update(
            unionId: $place->getId(),
            data: $this->unionUnifier->unifyOne(null, $place->toArray())
        );

        $this->eventPublisher->handle(PlaceQueue::UPDATED, $place->getId());
    }
}

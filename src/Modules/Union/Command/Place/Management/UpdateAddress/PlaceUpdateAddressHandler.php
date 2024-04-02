<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Place\Management\UpdateAddress;

use App\Http\Action\Unifier\Union\UnionUnifier;
use App\Modules\Union\Entity\Union\UnionRepository;
use App\Modules\Union\Entity\UnionPlaceInfo\UnionPlaceInfoRepository;
use App\Modules\Union\Event\Place\PlaceEventPublisher;
use App\Modules\Union\Event\Place\PlaceQueue;
use App\Modules\Union\Service\UnionRealtimeNotifier;
use ZayMedia\Shared\Components\Flusher;

final readonly class PlaceUpdateAddressHandler
{
    public function __construct(
        private UnionRepository $unionRepository,
        private UnionPlaceInfoRepository $unionPlaceInfoRepository,
        private UnionRealtimeNotifier $unionRealtimeNotifier,
        private UnionUnifier $unionUnifier,
        private Flusher $flusher,
        private PlaceEventPublisher $eventPublisher,
    ) {}

    public function handle(PlaceUpdateAddressCommand $command): void
    {
        $place = $this->unionRepository->getById($command->unionId);
        $placeInfo = $this->unionPlaceInfoRepository->getByUnionId($place->getId());

        $place->setCityId($command->cityId);

        $placeInfo->setLocation($command->address);
        $placeInfo->setGeolocation($command->latitude, $command->longitude);

        $this->flusher->flush();

        $this->unionRealtimeNotifier->update(
            unionId: $place->getId(),
            data: $this->unionUnifier->unifyOne(null, $place->toArray())
        );

        $this->eventPublisher->handle(PlaceQueue::UPDATED, $place->getId());
    }
}

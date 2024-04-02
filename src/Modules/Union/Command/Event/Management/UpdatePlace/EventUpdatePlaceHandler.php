<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Event\Management\UpdatePlace;

use App\Modules\Union\Entity\Union\UnionRepository;
use App\Modules\Union\Entity\UnionEventInfo\UnionEventInfo;
use App\Modules\Union\Entity\UnionEventInfo\UnionEventInfoRepository;
use App\Modules\Union\Event\Event\EventEventPublisher;
use App\Modules\Union\Event\Event\EventQueue;
use ZayMedia\Shared\Components\Flusher;

final readonly class EventUpdatePlaceHandler
{
    public function __construct(
        private UnionRepository $unionRepository,
        private UnionEventInfoRepository $unionEventInfoRepository,
        private EventEventPublisher $eventPublisher,
        private Flusher $flusher,
    ) {}

    public function handle(EventUpdatePlaceCommand $command): void
    {
        $event = $this->unionRepository->getById($command->unionId);

        $oldPlaceId = $this->unionEventInfoRepository->findPlaceIdByEventId($event->getId());

        if (null === $oldPlaceId) {
            $this->createInfo($event->getId(), $command->placeId);
        } else {
            $this->unionEventInfoRepository->updateAllPlaceByEventId(
                unionId: $event->getId(),
                placeId: $command->placeId
            );
        }

        $this->eventPublisher->handle(EventQueue::UPDATED, $event->getId());
    }

    private function createInfo(int $eventId, int $placeId): void
    {
        $time = time();

        $unionEventInfo = UnionEventInfo::create(
            unionId: $eventId,
            placeId: $placeId,
            timeStart: $time,
            timeEnd: strtotime(date('Y-m-d 23:59:59', $time)),
        );

        $this->unionEventInfoRepository->add($unionEventInfo);

        $this->flusher->flush();
    }
}

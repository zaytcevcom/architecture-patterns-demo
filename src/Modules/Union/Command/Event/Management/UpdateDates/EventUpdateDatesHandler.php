<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Event\Management\UpdateDates;

use App\Modules\Union\Entity\Union\UnionRepository;
use App\Modules\Union\Entity\UnionEventInfo\UnionEventInfo;
use App\Modules\Union\Entity\UnionEventInfo\UnionEventInfoRepository;
use App\Modules\Union\Event\Event\EventEventPublisher;
use App\Modules\Union\Event\Event\EventQueue;
use ZayMedia\Shared\Components\Flusher;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

final readonly class EventUpdateDatesHandler
{
    public function __construct(
        private UnionRepository $unionRepository,
        private UnionEventInfoRepository $unionEventInfoRepository,
        private Flusher $flusher,
        private EventEventPublisher $eventPublisher,
    ) {}

    public function handle(EventUpdateDatesCommand $command): void
    {
        if (empty($command->dates)) {
            throw new DomainExceptionModule(
                module: 'union',
                message: 'error.union.empty_dates',
                code: 1
            );
        }

        $event = $this->unionRepository->getById($command->unionId);

        $placeId = $this->unionEventInfoRepository->getPlaceIdByEventId($event->getId());

        $this->unionEventInfoRepository->removeAllEventsByPlaceId($event->getId(), $placeId);

        $this->addInfo($placeId, $event->getId(), $command->dates);

        $this->eventPublisher->handle(EventQueue::UPDATED, $event->getId());
    }

    private function addInfo(int $placeId, int $eventId, array $dates): void
    {
        /** @var array{timeStart: int, timeEnd: int} $date */
        foreach ($dates as $date) {
            $unionEventInfo = UnionEventInfo::create(
                unionId: $eventId,
                placeId: $placeId,
                timeStart: $date['timeStart'],
                timeEnd: strtotime(date('Y-m-d 23:59:59', $date['timeStart'])),
            );

            $this->unionEventInfoRepository->add($unionEventInfo);
        }

        $this->flusher->flush();
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Event\Management\UpdateAgeLimit;

use App\Modules\Union\Entity\Union\UnionRepository;
use App\Modules\Union\Event\Event\EventEventPublisher;
use App\Modules\Union\Event\Event\EventQueue;
use ZayMedia\Shared\Components\Flusher;

final readonly class EventUpdateAgeLimitHandler
{
    public function __construct(
        private UnionRepository $unionRepository,
        private Flusher $flusher,
        private EventEventPublisher $eventPublisher,
    ) {}

    public function handle(EventUpdateAgeLimitCommand $command): void
    {
        $event = $this->unionRepository->getById($command->unionId);

        $event->setAgeLimit(
            ageLimit: $command->ageLimit
        );

        $this->flusher->flush();

        $this->eventPublisher->handle(EventQueue::UPDATED, $event->getId());
    }
}

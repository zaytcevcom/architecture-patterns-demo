<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Event\Management\UpdateSections;

use App\Modules\Union\Entity\Union\UnionRepository;
use App\Modules\Union\Entity\UnionSection\UnionSectionRepository;
use App\Modules\Union\Event\Event\EventEventPublisher;
use App\Modules\Union\Event\Event\EventQueue;
use ZayMedia\Shared\Components\Flusher;

final readonly class EventUpdateSectionsHandler
{
    public function __construct(
        private UnionRepository $unionRepository,
        private UnionSectionRepository $unionSectionRepository,
        private Flusher $flusher,
        private EventEventPublisher $eventPublisher,
    ) {}

    public function handle(EventUpdateSectionsCommand $command): void
    {
        $event = $this->unionRepository->getById($command->unionId);
        $eventSections = $this->unionSectionRepository->getByUnionId($event->getId());

        $eventSections->edit(
            posts: $command->posts,
            photos: $command->photos,
            videos: $command->videos,
            audios: $command->audios,
            contacts: $command->contacts,
            links: $command->links,
            messages: $command->messages
        );

        $this->flusher->flush();

        $this->eventPublisher->handle(EventQueue::UPDATED, $event->getId());
    }
}

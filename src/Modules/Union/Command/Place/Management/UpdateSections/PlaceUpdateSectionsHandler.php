<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Place\Management\UpdateSections;

use App\Http\Action\Unifier\Union\UnionUnifier;
use App\Modules\Union\Entity\Union\UnionRepository;
use App\Modules\Union\Entity\UnionSection\UnionSectionRepository;
use App\Modules\Union\Event\Place\PlaceEventPublisher;
use App\Modules\Union\Event\Place\PlaceQueue;
use App\Modules\Union\Service\UnionRealtimeNotifier;
use ZayMedia\Shared\Components\Flusher;

final readonly class PlaceUpdateSectionsHandler
{
    public function __construct(
        private UnionRepository $unionRepository,
        private UnionSectionRepository $unionSectionRepository,
        private UnionRealtimeNotifier $unionRealtimeNotifier,
        private UnionUnifier $unionUnifier,
        private Flusher $flusher,
        private PlaceEventPublisher $eventPublisher,
    ) {}

    public function handle(PlaceUpdateSectionsCommand $command): void
    {
        $place = $this->unionRepository->getById($command->unionId);
        $placeSections = $this->unionSectionRepository->getByUnionId($place->getId());

        $placeSections->edit(
            posts: $command->posts,
            photos: $command->photos,
            videos: $command->videos,
            audios: $command->audios,
            contacts: $command->contacts,
            links: $command->links,
            messages: $command->messages
        );

        $this->flusher->flush();

        $this->unionRealtimeNotifier->update(
            unionId: $place->getId(),
            data: $this->unionUnifier->unifyOne(null, $place->toArray())
        );

        $this->eventPublisher->handle(PlaceQueue::UPDATED, $place->getId());
    }
}

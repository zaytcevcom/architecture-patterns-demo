<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Community\Management\UpdateSections;

use App\Modules\Union\Entity\Union\UnionRepository;
use App\Modules\Union\Entity\UnionSection\UnionSectionRepository;
use App\Modules\Union\Event\Community\CommunityEventPublisher;
use App\Modules\Union\Event\Community\CommunityQueue;
use ZayMedia\Shared\Components\Flusher;

final readonly class CommunityUpdateSectionsHandler
{
    public function __construct(
        private UnionRepository $unionRepository,
        private UnionSectionRepository $unionSectionRepository,
        private Flusher $flusher,
        private CommunityEventPublisher $eventPublisher,
    ) {}

    public function handle(CommunityUpdateSectionsCommand $command): void
    {
        $community = $this->unionRepository->getById($command->unionId);
        $communitySections = $this->unionSectionRepository->getByUnionId($community->getId());

        $communitySections->edit(
            posts: $command->posts,
            photos: $command->photos,
            videos: $command->videos,
            audios: $command->audios,
            contacts: $command->contacts,
            links: $command->links,
            messages: $command->messages
        );

        $this->flusher->flush();

        $this->eventPublisher->handle(CommunityQueue::UPDATED, $community->getId());
    }
}

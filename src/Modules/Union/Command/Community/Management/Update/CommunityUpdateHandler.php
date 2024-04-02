<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Community\Management\Update;

use App\Http\Action\Unifier\Union\UnionUnifier;
use App\Modules\Union\Entity\Union\UnionRepository;
use App\Modules\Union\Event\Community\CommunityEventPublisher;
use App\Modules\Union\Event\Community\CommunityQueue;
use App\Modules\Union\Service\UnionRealtimeNotifier;
use ZayMedia\Shared\Components\Flusher;

final readonly class CommunityUpdateHandler
{
    public function __construct(
        private UnionRepository $unionRepository,
        private UnionRealtimeNotifier $unionRealtimeNotifier,
        private UnionUnifier $unionUnifier,
        private Flusher $flusher,
        private CommunityEventPublisher $eventPublisher,
    ) {}

    public function handle(CommunityUpdateCommand $command): void
    {
        $community = $this->unionRepository->getById($command->unionId);

        $community->edit(
            name: $command->name,
            description: $command->description,
            categoryId: $command->categoryId,
            website: $command->website,
            status: $command->status,
        );

        $community->setAgeLimit($command->ageLimit);
        $community->setCityId($command->cityId);

        $this->flusher->flush();

        $this->unionRealtimeNotifier->update(
            unionId: $community->getId(),
            data: $this->unionUnifier->unifyOne(null, $community->toArray())
        );

        $this->eventPublisher->handle(CommunityQueue::UPDATED, $community->getId());
    }
}

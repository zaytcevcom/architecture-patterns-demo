<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Community\Management\UpdatePrivacy;

use App\Modules\Union\Entity\Union\UnionRepository;
use App\Modules\Union\Event\Community\CommunityEventPublisher;
use App\Modules\Union\Event\Community\CommunityQueue;
use ZayMedia\Shared\Components\Flusher;

final readonly class CommunityUpdatePrivacyHandler
{
    public function __construct(
        private UnionRepository $unionRepository,
        private Flusher $flusher,
        private CommunityEventPublisher $eventPublisher,
    ) {}

    public function handle(CommunityUpdatePrivacyCommand $command): void
    {
        $community = $this->unionRepository->getById($command->unionId);

        $community->editPrivacy(
            kind: $command->kind,
            membersHide: $command->membersHide,
        );

        $this->flusher->flush();

        $this->eventPublisher->handle(CommunityQueue::UPDATED, $community->getId());
    }
}

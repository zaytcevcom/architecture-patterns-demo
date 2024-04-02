<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\UpdateCounter;

use App\Http\Action\Unifier\Union\UnionUnifier;
use App\Modules\Union\Entity\Union\UnionRepository;
use App\Modules\Union\Entity\UnionUser\UnionUserRepository;
use App\Modules\Union\Service\UnionRealtimeNotifier;
use ZayMedia\Shared\Components\Flusher;

final readonly class UnionUpdateCounterMembersHandler
{
    public function __construct(
        private UnionRepository $unionRepository,
        private UnionUserRepository $unionUserRepository,
        private Flusher $flusher,
        private UnionRealtimeNotifier $unionRealtimeNotifier,
        private UnionUnifier $unionUnifier,
    ) {}

    public function handle(int $id): void
    {
        $union = $this->unionRepository->getById($id);

        $union->setCountMembers(
            $this->unionUserRepository->countByUnionId($union->getId())
        );

        $this->flusher->flush();

        $this->unionRealtimeNotifier->update(
            unionId: $union->getId(),
            data: $this->unionUnifier->unifyOne(null, $union->toArray())
        );
    }
}

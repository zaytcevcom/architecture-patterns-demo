<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\UpdateCounter;

use App\Http\Action\Unifier\Union\UnionUnifier;
use App\Modules\Audio\Entity\Audio\AudioRepository;
use App\Modules\Union\Entity\Union\UnionRepository;
use App\Modules\Union\Service\UnionRealtimeNotifier;
use ZayMedia\Shared\Components\Flusher;

final readonly class UnionUpdateCounterAudiosHandler
{
    public function __construct(
        private UnionRepository $unionRepository,
        private AudioRepository $audioRepository,
        private Flusher $flusher,
        private UnionRealtimeNotifier $unionRealtimeNotifier,
        private UnionUnifier $unionUnifier,
    ) {}

    public function handle(int $id): void
    {
        $union = $this->unionRepository->getById($id);

        $union->setCountAudios(
            $this->audioRepository->countByUnionId($union->getId())
        );

        $this->flusher->flush();

        $this->unionRealtimeNotifier->update(
            unionId: $union->getId(),
            data: $this->unionUnifier->unifyOne(null, $union->toArray())
        );
    }
}

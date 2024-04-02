<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\UpdateCounter;

use App\Http\Action\Unifier\Union\UnionUnifier;
use App\Modules\Post\Entity\Post\PostRepository;
use App\Modules\Union\Entity\Union\UnionRepository;
use App\Modules\Union\Service\UnionRealtimeNotifier;
use ZayMedia\Shared\Components\Flusher;

final readonly class UnionUpdateCounterPostsHandler
{
    public function __construct(
        private UnionRepository $unionRepository,
        private PostRepository $postRepository,
        private Flusher $flusher,
        private UnionRealtimeNotifier $unionRealtimeNotifier,
        private UnionUnifier $unionUnifier,
    ) {}

    public function handle(int $id): void
    {
        $union = $this->unionRepository->getById($id);

        $union->setCountPosts(
            $this->postRepository->countByUnionId($union->getId())
        );

        $this->flusher->flush();

        $this->unionRealtimeNotifier->update(
            unionId: $union->getId(),
            data: $this->unionUnifier->unifyOne(null, $union->toArray())
        );
    }
}

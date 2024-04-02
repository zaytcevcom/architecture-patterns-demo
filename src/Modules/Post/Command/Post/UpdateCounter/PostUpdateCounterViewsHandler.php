<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\Post\UpdateCounter;

use App\Http\Action\Unifier\Post\PostUnifier;
use App\Modules\Post\Entity\Post\PostRepository;
use App\Modules\Post\Entity\PostView\PostViewRepository;
use App\Modules\Post\Service\PostRealtimeNotifier;
use ZayMedia\Shared\Components\Flusher;

final readonly class PostUpdateCounterViewsHandler
{
    public function __construct(
        private PostRepository $postRepository,
        private PostViewRepository $postViewRepository,
        private Flusher $flusher,
        private PostRealtimeNotifier $postRealtimeNotifier,
        private PostUnifier $postUnifier,
    ) {}

    public function handle(int $id): void
    {
        $post = $this->postRepository->getById($id);

        $post->setCountViews(
            $this->postViewRepository->countByPost($post->getId())
        );

        $this->flusher->flush();

        $this->postRealtimeNotifier->update(
            postId: $post->getId(),
            data: $this->postUnifier->unifyOne(null, $post->toArray())
        );
    }
}

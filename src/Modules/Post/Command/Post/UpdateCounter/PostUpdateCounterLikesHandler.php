<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\Post\UpdateCounter;

use App\Http\Action\Unifier\Post\PostUnifier;
use App\Modules\Post\Entity\Post\PostRepository;
use App\Modules\Post\Entity\PostLike\PostLikeRepository;
use App\Modules\Post\Service\PostRealtimeNotifier;
use ZayMedia\Shared\Components\Flusher;

final readonly class PostUpdateCounterLikesHandler
{
    public function __construct(
        private PostRepository $postRepository,
        private PostLikeRepository $postLikeRepository,
        private Flusher $flusher,
        private PostRealtimeNotifier $postRealtimeNotifier,
        private PostUnifier $postUnifier,
    ) {}

    public function handle(int $id): void
    {
        $post = $this->postRepository->getById($id);

        $post->setCountLikes(
            $this->postLikeRepository->countByPost($post->getId())
        );

        $this->flusher->flush();

        $this->postRealtimeNotifier->update(
            postId: $post->getId(),
            data: $this->postUnifier->unifyOne(null, $post->toArray())
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\Post\UpdateCounter;

use App\Http\Action\Unifier\Post\PostUnifier;
use App\Modules\Post\Entity\Post\PostRepository;
use App\Modules\Post\Entity\PostComment\PostCommentRepository;
use App\Modules\Post\Service\PostRealtimeNotifier;
use ZayMedia\Shared\Components\Flusher;

final readonly class PostUpdateCounterCommentsHandler
{
    public function __construct(
        private PostRepository $postRepository,
        private PostCommentRepository $postCommentRepository,
        private Flusher $flusher,
        private PostRealtimeNotifier $postRealtimeNotifier,
        private PostUnifier $postUnifier,
    ) {}

    public function handle(int $id): void
    {
        $post = $this->postRepository->getById($id);

        $post->setCountComments(
            $this->postCommentRepository->countByPostId($post->getId())
        );

        $this->flusher->flush();

        $this->postRealtimeNotifier->update(
            postId: $post->getId(),
            data: $this->postUnifier->unifyOne(null, $post->toArray())
        );
    }
}

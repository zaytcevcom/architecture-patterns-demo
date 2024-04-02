<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\Post\Delete;

use App\Modules\Identity\Command\UpdateCounter\IdentityUpdateCounterPostsHandler;
use App\Modules\Post\Command\Post\UpdateCounter\PostUpdateCounterRepostsHandler;
use App\Modules\Post\Entity\Post\PostRepository;
use App\Modules\Post\Service\PostRealtimeNotifier;
use App\Modules\Union\Command\UpdateCounter\UnionUpdateCounterPostsHandler;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use ZayMedia\Shared\Components\Flusher;

final readonly class PostDeleteHandler
{
    public function __construct(
        private PostRepository $postRepository,
        private IdentityUpdateCounterPostsHandler $identityUpdateCounterPostsHandler,
        private UnionUpdateCounterPostsHandler $unionUpdateCounterPostsHandler,
        private PostUpdateCounterRepostsHandler $postUpdateCounterRepostsHandler,
        private PostRealtimeNotifier $postRealtimeNotifier,
        private Flusher $flusher
    ) {}

    public function handle(PostDeleteCommand $command): void
    {
        $post = $this->postRepository->getById($command->postId);

        // todo: permissions
        if ($post->getUserId() !== $command->userId) {
            throw new AccessDeniedException();
        }

        $post->markDeleted();

        $this->flusher->flush();

        if ($unionId = $post->getUnionId()) {
            $this->unionUpdateCounterPostsHandler->handle($unionId);
        } else {
            $this->identityUpdateCounterPostsHandler->handle($post->getUserId());
        }

        if ($postId = $post->getPostId()) {
            $this->postUpdateCounterRepostsHandler->handle($postId);
        }

        $this->postRealtimeNotifier->delete(
            postId: $post->getId(),
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\Post\Restore;

use App\Http\Action\Unifier\Post\PostUnifier;
use App\Modules\Identity\Command\UpdateCounter\IdentityUpdateCounterPostsHandler;
use App\Modules\Post\Command\Post\UpdateCounter\PostUpdateCounterRepostsHandler;
use App\Modules\Post\Entity\Post\PostRepository;
use App\Modules\Post\Service\PostRealtimeNotifier;
use App\Modules\Union\Command\UpdateCounter\UnionUpdateCounterPostsHandler;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use ZayMedia\Shared\Components\Flusher;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

final readonly class PostRestoreHandler
{
    public function __construct(
        private PostRepository $postRepository,
        private IdentityUpdateCounterPostsHandler $identityUpdateCounterPostsHandler,
        private UnionUpdateCounterPostsHandler $unionUpdateCounterPostsHandler,
        private PostUpdateCounterRepostsHandler $postUpdateCounterRepostsHandler,
        private PostRealtimeNotifier $postRealtimeNotifier,
        private PostUnifier $postUnifier,
        private Flusher $flusher
    ) {}

    public function handle(PostRestoreCommand $command): void
    {
        $post = $this->postRepository->getDeletedById($command->postId);

        // todo: permissions
        if ($post->getUserId() !== $command->userId) {
            throw new AccessDeniedException();
        }

        if (!$post->canRestore()) {
            throw new DomainExceptionModule(
                module: 'post',
                message: 'post.post.time_to_restore_has_expired',
                code: 4
            );
        }

        $post->restore();

        $this->flusher->flush();

        if ($unionId = $post->getUnionId()) {
            $this->unionUpdateCounterPostsHandler->handle($unionId);
        } else {
            $this->identityUpdateCounterPostsHandler->handle($post->getUserId());
        }

        if ($postId = $post->getPostId()) {
            $this->postUpdateCounterRepostsHandler->handle($postId);
        }

        $this->postRealtimeNotifier->restore(
            postId: $post->getId(),
            data: $this->postUnifier->unifyOne(null, $post->toArray())
        );
    }
}

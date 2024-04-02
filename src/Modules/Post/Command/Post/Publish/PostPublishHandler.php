<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\Post\Publish;

use App\Http\Action\Unifier\Post\PostUnifier;
use App\Modules\Identity\Command\UpdateCounter\IdentityUpdateCounterPostsHandler;
use App\Modules\Identity\Service\UserRealtimeNotifier;
use App\Modules\Post\Entity\Post\Post;
use App\Modules\Post\Entity\Post\PostRepository;
use App\Modules\Union\Command\UpdateCounter\UnionUpdateCounterPostsHandler;
use App\Modules\Union\Service\UnionRealtimeNotifier;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use ZayMedia\Shared\Components\Flusher;

final readonly class PostPublishHandler
{
    public function __construct(
        private PostRepository $postRepository,
        private IdentityUpdateCounterPostsHandler $identityUpdateCounterPostsHandler,
        private UnionUpdateCounterPostsHandler $unionUpdateCounterPostsHandler,
        private UserRealtimeNotifier $userRealtimeNotifier,
        private UnionRealtimeNotifier $unionRealtimeNotifier,
        private PostUnifier $postUnifier,
        private Flusher $flusher,
    ) {}

    public function handle(PostPublishCommand $command): Post
    {
        $post = $this->postRepository->getById($command->postId);

        // todo: permissions
        //        if ($post->getUserId() !== $command->userId) {
        //            throw new AccessDeniedException();
        //        }

        $post->publish();

        $this->postRepository->add($post);

        $this->flusher->flush();

        if ($unionId = $post->getUnionId()) {
            $this->unionUpdateCounterPostsHandler->handle($unionId);

            $this->unionRealtimeNotifier->newPost(
                unionId: $unionId,
                data: $this->postUnifier->unifyOne(null, $post->toArray())
            );
        } else {
            $this->identityUpdateCounterPostsHandler->handle($post->getUserId());

            $this->userRealtimeNotifier->newPost(
                userId: $post->getUserId(),
                data: $this->postUnifier->unifyOne(null, $post->toArray())
            );
        }

        return $post;
    }
}

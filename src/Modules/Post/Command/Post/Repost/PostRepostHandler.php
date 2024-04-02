<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\Post\Repost;

use App\Http\Action\Unifier\Post\PostUnifier;
use App\Modules\Identity\Command\UpdateCounter\IdentityUpdateCounterPostsHandler;
use App\Modules\Identity\Entity\User\User;
use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Identity\Service\UserRealtimeNotifier;
use App\Modules\Notifier\Command\Post\PostReposted\PostRepostedCommand;
use App\Modules\Notifier\Command\Post\PostReposted\PostRepostedHandler;
use App\Modules\Post\Command\Post\UpdateCounter\PostUpdateCounterRepostsHandler;
use App\Modules\Post\Entity\Post\Post;
use App\Modules\Post\Entity\Post\PostRepository;
use App\Modules\Union\Command\UpdateCounter\UnionUpdateCounterPostsHandler;
use App\Modules\Union\Entity\Union\UnionRepository;
use App\Modules\Union\Service\UnionRealtimeNotifier;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use ZayMedia\Shared\Components\Flusher;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

final readonly class PostRepostHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private UnionRepository $unionRepository,
        private PostRepository $postRepository,
        private IdentityUpdateCounterPostsHandler $identityUpdateCounterPostsHandler,
        private UnionUpdateCounterPostsHandler $unionUpdateCounterPostsHandler,
        private PostUpdateCounterRepostsHandler $postUpdateCounterRepostsHandler,
        private UserRealtimeNotifier $userRealtimeNotifier,
        private UnionRealtimeNotifier $unionRealtimeNotifier,
        private PostRepostedHandler $repostHandler,
        private PostUnifier $postUnifier,
        private Flusher $flusher
    ) {}

    public function handle(PostRepostCommand $command): Post
    {
        $post = $this->postRepository->getById($command->postId);
        $user = $this->userRepository->getById($command->userId);
        $union = (!empty($command->unionId)) ? $this->unionRepository->getById($command->unionId) : null;

        // Check max limit
        if ($this->postRepository->countByUserId($user->getId()) >= Post::limitTotal()) {
            throw new DomainExceptionModule(
                module: 'post',
                message: 'error.post.limit_total',
                code: 2
            );
        }

        // Check daily limit
        if ($this->postRepository->countTodayByUserId($user->getId()) >= Post::limitDaily()) {
            throw new DomainExceptionModule(
                module: 'post',
                message: 'error.post.limit_daily',
                code: 3
            );
        }

        if ($command->uniqueTime !== null) {
            $ownerId = !empty($union) ? -1 * $union->getId() : $user->getId();

            if ($this->postRepository->findByUniqueTime($command->uniqueTime, $ownerId)) {
                throw new DomainExceptionModule(
                    module: 'post',
                    message: 'error.post.duplicate_post',
                    code: 4
                );
            }
        }

        if ($user->getId() === $post->getUserId() && $post->getUnionId() === null && $union?->getId() === null) {
            throw new AccessDeniedException();
        }

        $postId = $post->getPostId() ?? $post->getId();

        if ($union?->getId() === null) {
            if ($this->postRepository->isRepostedByUser($postId, $user->getId())) {
                throw new DomainExceptionModule(
                    module: 'post',
                    message: 'error.post.already_reposted',
                    code: 5
                );
            }
        }

        $repost = Post::repost(
            userId: $user->getId(),
            unionId: $union?->getId(),
            postId: $postId,
            message: $command->message,
            time: $command->time,
            uniqueTime: $command->uniqueTime,
            closeComments: $command->closeComments,
            contactsOnly: $command->contactsOnly
        );

        $this->postRepository->add($repost);

        $this->flusher->flush();

        $this->updateData($user, $repost);

        $this->postUpdateCounterRepostsHandler->handle($postId);

        return $repost;
    }

    private function updateData(User $user, Post $post): void
    {
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

            $this->repostHandler->handle(
                new PostRepostedCommand(
                    userId: $user->getId(),
                    postId: (int)$post->getPostId()
                )
            );
        }
    }
}

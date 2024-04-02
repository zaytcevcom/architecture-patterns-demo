<?php

declare(strict_types=1);

namespace App\Modules\Post\Event\Post\Liked;

use App\Modules\Identity\Entity\User\User;
use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Notifier\Command\Post\PostLiked\PostLikedCommand;
use App\Modules\Notifier\Command\Post\PostLiked\PostLikedHandler;
use App\Modules\Post\Command\Post\UpdateCounter\PostUpdateCounterLikesHandler;
use App\Modules\Post\Entity\Post\Post;
use App\Modules\Post\Entity\Post\PostRepository;
use App\Modules\Post\Entity\PostLike\PostLikeRepository;

final readonly class PostLikedEvent
{
    public function __construct(
        private UserRepository $userRepository,
        private PostRepository $postRepository,
        private PostLikeRepository $postLikeRepository,
        private PostUpdateCounterLikesHandler $postUpdateCounterLikesHandler,
        private PostLikedHandler $postLikedHandler,
    ) {}

    public function handle(PostLikedData $command): void
    {
        $user = $this->userRepository->getById($command->userId);
        $post = $this->postRepository->getById($command->postId);

        $this->updateCounters($post->getId());
        $this->sendPush($post, $user);
    }

    private function updateCounters(int $postId): void
    {
        $this->postUpdateCounterLikesHandler->handle($postId);
    }

    private function sendPush(Post $post, User $user): void
    {
        if (null !== $post->getUnionId() || $post->getUserId() === $user->getId()) {
            return;
        }

        $postLike = $this->postLikeRepository->findByPostAndUserIds($post->getId(), $user->getId());

        if (null === $postLike) {
            return;
        }

        $this->postLikedHandler->handle(
            new PostLikedCommand(
                userId: $user->getId(),
                postId: $post->getId(),
                likeId: $postLike->getId()
            )
        );
    }
}

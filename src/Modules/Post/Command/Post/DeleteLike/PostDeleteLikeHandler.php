<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\Post\DeleteLike;

use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Notifier\Command\Post\PostLikedRemove\PostLikedRemoveCommand;
use App\Modules\Notifier\Command\Post\PostLikedRemove\PostLikedRemoveHandler;
use App\Modules\Post\Command\Post\UpdateCounter\PostUpdateCounterLikesHandler;
use App\Modules\Post\Entity\Post\PostRepository;
use App\Modules\Post\Entity\PostLike\PostLikeRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class PostDeleteLikeHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private PostRepository $postRepository,
        private PostLikeRepository $postLikeRepository,
        private PostUpdateCounterLikesHandler $postUpdateCounterLikesHandler,
        private PostLikedRemoveHandler $postLikedRemoveHandler,
        private Flusher $flusher
    ) {}

    public function handle(PostDeleteLikeCommand $command): void
    {
        $user = $this->userRepository->getById($command->userId);
        $post = $this->postRepository->getById($command->postId);

        $postLike = $this->postLikeRepository->findByPostAndUserIds(
            postId: $post->getId(),
            userId: $user->getId()
        );

        if (empty($postLike)) {
            return;
        }

        $likeId = $postLike->getId();

        $this->postLikeRepository->remove($postLike);

        $this->flusher->flush();

        // Update post counter likes
        $this->postUpdateCounterLikesHandler->handle($post->getId());

        if (null === $post->getUnionId() && $post->getUserId() !== $user->getId()) {
            $this->postLikedRemoveHandler->handle(
                new PostLikedRemoveCommand(
                    userId: $post->getUserId(),
                    likeId: $likeId
                )
            );
        }
    }
}

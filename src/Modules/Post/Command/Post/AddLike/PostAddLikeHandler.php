<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\Post\AddLike;

use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Post\Entity\Post\PostRepository;
use App\Modules\Post\Entity\PostLike\PostLike;
use App\Modules\Post\Entity\PostLike\PostLikeRepository;
use App\Modules\Post\Event\Post\Liked\PostLikedData;
use App\Modules\Post\Event\Post\Liked\PostLikedEvent;
use ZayMedia\Shared\Components\Flusher;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

final readonly class PostAddLikeHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private PostRepository $postRepository,
        private PostLikeRepository $postLikeRepository,
        private PostLikedEvent $postLikedEvent,
        private Flusher $flusher
    ) {}

    public function handle(PostAddLikeCommand $command): void
    {
        $user = $this->userRepository->getById($command->userId);
        $post = $this->postRepository->getById($command->postId);

        // Check max limit
        if (!$user->isBot() && $this->postLikeRepository->countByUserId($user->getId()) >= PostLike::limitTotal()) {
            throw new DomainExceptionModule(
                module: 'post',
                message: 'error.post_like.limit_total',
                code: 2
            );
        }

        // Check daily limit
        if (!$user->isBot() && $this->postLikeRepository->countTodayByUserId($user->getId()) >= PostLike::limitDaily()) {
            throw new DomainExceptionModule(
                module: 'post',
                message: 'error.post_like.limit_daily',
                code: 3
            );
        }

        $postLike = $this->postLikeRepository->findByPostAndUserIds(
            postId: $post->getId(),
            userId: $user->getId()
        );

        if (!empty($postLike)) {
            return;
        }

        $postLike = PostLike::create(
            userId: $user->getId(),
            postId: $post->getId()
        );

        $this->postLikeRepository->add($postLike);

        $this->flusher->flush();

        $this->postLikedEvent->handle(
            new PostLikedData(
                userId: $user->getId(),
                postId: $post->getId()
            )
        );
    }
}

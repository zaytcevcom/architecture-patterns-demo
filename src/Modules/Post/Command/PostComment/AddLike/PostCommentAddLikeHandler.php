<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\PostComment\AddLike;

use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Notifier\Command\Post\PostCommentLiked\PostCommentLikedCommand;
use App\Modules\Notifier\Command\Post\PostCommentLiked\PostCommentLikedHandler;
use App\Modules\Post\Command\PostComment\UpdateCounter\PostCommentUpdateCounterLikesHandler;
use App\Modules\Post\Entity\PostComment\PostCommentRepository;
use App\Modules\Post\Entity\PostCommentLike\PostCommentLike;
use App\Modules\Post\Entity\PostCommentLike\PostCommentLikeRepository;
use ZayMedia\Shared\Components\Flusher;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

final readonly class PostCommentAddLikeHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private PostCommentRepository $postCommentRepository,
        private PostCommentLikeRepository $postCommentLikeRepository,
        private PostCommentUpdateCounterLikesHandler $postCommentUpdateCounterLikesHandler,
        private PostCommentLikedHandler $postCommentLikedHandler,
        private Flusher $flusher
    ) {}

    public function handle(PostCommentAddLikeCommand $command): void
    {
        $user = $this->userRepository->getById($command->userId);
        $comment = $this->postCommentRepository->getById($command->commentId);

        $this->checkLimits($user->getId());

        $postCommentLike = $this->postCommentLikeRepository->findByCommentAndUserIds(
            commentId: $comment->getId(),
            userId: $user->getId()
        );

        if (!empty($postCommentLike)) {
            return;
        }

        $postCommentLike = PostCommentLike::create(
            userId: $user->getId(),
            commentId: $comment->getId()
        );

        $this->postCommentLikeRepository->add($postCommentLike);

        $this->flusher->flush();

        // Update comment counter likes
        $this->postCommentUpdateCounterLikesHandler->handle($comment->getId());

        if (null === $comment->getUnionId() && $comment->getUserId() !== $user->getId()) {
            $this->postCommentLikedHandler->handle(
                new PostCommentLikedCommand(
                    userId: $user->getId(),
                    commentId: $comment->getId(),
                    likeId: $postCommentLike->getId()
                )
            );
        }
    }

    private function checkLimits(int $userId): void
    {
        // Check max limit
        if ($this->postCommentLikeRepository->countByUserId($userId) >= PostCommentLike::limitTotal()) {
            throw new DomainExceptionModule(
                module: 'post',
                message: 'error.post_comment_like.limit_total',
                code: 2
            );
        }

        // Check daily limit
        if ($this->postCommentLikeRepository->countTodayByUserId($userId) >= PostCommentLike::limitDaily()) {
            throw new DomainExceptionModule(
                module: 'post',
                message: 'error.post_comment_like.limit_daily',
                code: 3
            );
        }
    }
}

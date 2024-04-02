<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\PostComment\DeleteLike;

use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Post\Command\PostComment\UpdateCounter\PostCommentUpdateCounterLikesHandler;
use App\Modules\Post\Entity\PostComment\PostCommentRepository;
use App\Modules\Post\Entity\PostCommentLike\PostCommentLikeRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class PostCommentDeleteLikeHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private PostCommentRepository $postCommentRepository,
        private PostCommentLikeRepository $postCommentLikeRepository,
        private PostCommentUpdateCounterLikesHandler $postCommentUpdateCounterLikesHandler,
        private Flusher $flusher
    ) {}

    public function handle(PostCommentDeleteLikeCommand $command): void
    {
        $user = $this->userRepository->getById($command->userId);
        $comment = $this->postCommentRepository->getById($command->commentId);

        $postCommentLike = $this->postCommentLikeRepository->findByCommentAndUserIds(
            commentId: $comment->getId(),
            userId: $user->getId()
        );

        if (empty($postCommentLike)) {
            return;
        }

        $this->postCommentLikeRepository->remove($postCommentLike);

        $this->flusher->flush();

        // Update comment counter likes
        $this->postCommentUpdateCounterLikesHandler->handle($comment->getId());
    }
}

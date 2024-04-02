<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\PostComment\UpdateCounter;

use App\Modules\Post\Entity\PostComment\PostCommentRepository;
use App\Modules\Post\Entity\PostCommentLike\PostCommentLikeRepository;
use ZayMedia\Shared\Components\Flusher;

final readonly class PostCommentUpdateCounterLikesHandler
{
    public function __construct(
        private PostCommentRepository $postCommentRepository,
        private PostCommentLikeRepository $postCommentLikeRepository,
        private Flusher $flusher
    ) {}

    public function handle(int $id): void
    {
        $postComment = $this->postCommentRepository->getById($id);

        $postComment->setCountLikes(
            $this->postCommentLikeRepository->countByCommentId($postComment->getId())
        );

        $this->flusher->flush();
    }
}

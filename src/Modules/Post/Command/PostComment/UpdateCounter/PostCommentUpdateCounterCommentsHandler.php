<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\PostComment\UpdateCounter;

use App\Http\Action\Unifier\Post\PostCommentUnifier;
use App\Modules\Post\Entity\PostComment\PostCommentRepository;
use App\Modules\Post\Service\PostRealtimeNotifier;
use ZayMedia\Shared\Components\Flusher;

final readonly class PostCommentUpdateCounterCommentsHandler
{
    public function __construct(
        private PostCommentRepository $postCommentRepository,
        private PostRealtimeNotifier $postRealtimeNotifier,
        private PostCommentUnifier $postCommentUnifier,
        private Flusher $flusher
    ) {}

    public function handle(int $id): void
    {
        $postComment = $this->postCommentRepository->getById($id);

        $postComment->setCountComments(
            $this->postCommentRepository->countByCommentId($postComment->getId())
        );

        $this->flusher->flush();

        $this->postRealtimeNotifier->updateComment(
            postId: $postComment->getPostId(),
            data: $this->postCommentUnifier->unifyOne(null, $postComment->toArray())
        );
    }
}

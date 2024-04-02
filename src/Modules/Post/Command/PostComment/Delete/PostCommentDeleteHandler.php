<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\PostComment\Delete;

use App\Http\Action\Unifier\Post\PostCommentUnifier;
use App\Modules\Post\Command\Post\UpdateCounter\PostUpdateCounterCommentsHandler;
use App\Modules\Post\Entity\PostComment\PostCommentRepository;
use App\Modules\Post\Service\PostRealtimeNotifier;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use ZayMedia\Shared\Components\Flusher;

final readonly class PostCommentDeleteHandler
{
    public function __construct(
        private PostCommentRepository $postCommentRepository,
        private PostUpdateCounterCommentsHandler $postUpdateCounterCommentsHandler,
        private PostRealtimeNotifier $postRealtimeNotifier,
        private PostCommentUnifier $postCommentUnifier,
        private Flusher $flusher
    ) {}

    public function handle(PostCommentDeleteCommand $command): void
    {
        $postComment = $this->postCommentRepository->getById($command->commentId);

        if ($postComment->getUserId() !== $command->userId) {
            throw new AccessDeniedException();
        }

        $postComment->markDeleted();

        $this->flusher->flush();

        $this->postUpdateCounterCommentsHandler->handle($postComment->getPostId());

        $this->postRealtimeNotifier->deleteComment(
            postId: $postComment->getPostId(),
            data: $this->postCommentUnifier->unifyOne(null, $postComment->toArray())
        );
    }
}

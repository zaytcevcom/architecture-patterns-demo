<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\PostComment\Restore;

use App\Http\Action\Unifier\Post\PostCommentUnifier;
use App\Modules\Post\Command\Post\UpdateCounter\PostUpdateCounterCommentsHandler;
use App\Modules\Post\Entity\PostComment\PostCommentRepository;
use App\Modules\Post\Service\PostRealtimeNotifier;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use ZayMedia\Shared\Components\Flusher;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

final readonly class PostCommentRestoreHandler
{
    public function __construct(
        private PostCommentRepository $postCommentRepository,
        private PostUpdateCounterCommentsHandler $postUpdateCounterCommentsHandler,
        private PostRealtimeNotifier $postRealtimeNotifier,
        private PostCommentUnifier $postCommentUnifier,
        private Flusher $flusher
    ) {}

    public function handle(PostCommentRestoreCommand $command): void
    {
        $postComment = $this->postCommentRepository->getById($command->commentId);

        if ($postComment->getUserId() !== $command->userId) {
            throw new AccessDeniedException();
        }

        // Can restore
        if (!$postComment->canRestore()) {
            throw new DomainExceptionModule(
                module: 'post',
                message: 'post.post_comment.time_to_restore_has_expired',
                code: 4
            );
        }

        $postComment->restore();

        $this->flusher->flush();

        $this->postUpdateCounterCommentsHandler->handle($postComment->getPostId());

        $this->postRealtimeNotifier->restoreComment(
            postId: $postComment->getPostId(),
            data: $this->postCommentUnifier->unifyOne(null, $postComment->toArray())
        );
    }
}

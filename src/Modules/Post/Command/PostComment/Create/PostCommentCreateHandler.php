<?php

declare(strict_types=1);

namespace App\Modules\Post\Command\PostComment\Create;

use App\Http\Action\Unifier\Post\PostCommentUnifier;
use App\Modules\Audio\Query\Audio\GetByIds\AudioGetByIdsFetcher;
use App\Modules\Audio\Query\Audio\GetByIds\AudioGetByIdsQuery;
use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Media\Query\Video\GetByIds\VideoGetByIdsFetcher;
use App\Modules\Media\Query\Video\GetByIds\VideoGetByIdsQuery;
use App\Modules\Notifier\Command\Post\PostCommentAnswered\PostCommentAnsweredCommand;
use App\Modules\Notifier\Command\Post\PostCommentAnswered\PostCommentAnsweredHandler;
use App\Modules\Notifier\Command\Post\PostCommented\PostCommentedCommand;
use App\Modules\Notifier\Command\Post\PostCommented\PostCommentedHandler;
use App\Modules\Photo\Query\Photo\GetByIds\PhotoGetByIdsFetcher;
use App\Modules\Photo\Query\Photo\GetByIds\PhotoGetByIdsQuery;
use App\Modules\Post\Command\Post\UpdateCounter\PostUpdateCounterCommentsHandler;
use App\Modules\Post\Command\PostComment\UpdateCounter\PostCommentUpdateCounterCommentsHandler;
use App\Modules\Post\Entity\Post\PostRepository;
use App\Modules\Post\Entity\PostComment\PostComment;
use App\Modules\Post\Entity\PostComment\PostCommentRepository;
use App\Modules\Post\Service\PostRealtimeNotifier;
use App\Modules\Union\Entity\Union\UnionRepository;
use ZayMedia\Shared\Components\Flusher;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

final readonly class PostCommentCreateHandler
{
    public function __construct(
        private PhotoGetByIdsFetcher $photoGetByIdsFetcher,
        private AudioGetByIdsFetcher $audioGetByIdsFetcher,
        private VideoGetByIdsFetcher $videoGetByIdsFetcher,
        private PostRepository $postRepository,
        private UserRepository $userRepository,
        private UnionRepository $unionRepository,
        private PostCommentRepository $postCommentRepository,
        private PostUpdateCounterCommentsHandler $postUpdateCounterCommentsHandler,
        private PostCommentUpdateCounterCommentsHandler $postCommentUpdateCounterCommentsHandler,
        private PostRealtimeNotifier $postRealtimeNotifier,
        private PostCommentUnifier $postCommentUnifier,
        private PostCommentedHandler $postCommentedHandler,
        private PostCommentAnsweredHandler $postCommentAnsweredHandler,
        private Flusher $flusher
    ) {}

    public function handle(PostCommentCreateCommand $command): void
    {
        $post = $this->postRepository->getById($command->postId);
        $comment = (!empty($command->commentId)) ? $this->postCommentRepository->getById($command->commentId) : null;
        $user = $this->userRepository->getById($command->userId);
        $union = (!empty($command->unionId)) ? $this->unionRepository->getById($command->unionId) : null;

        $this->checkLimits($user->getId());

        if ($command->uniqueTime !== null) {
            if ($this->postCommentRepository->findByUniqueTime($post->getId(), $command->uniqueTime, $user->getId())) {
                throw new DomainExceptionModule(
                    module: 'post',
                    message: 'error.post_comment.duplicate_comment',
                    code: 4
                );
            }
        }

        $postComment = PostComment::create(
            postId: $post->getId(),
            userId: $user->getId(),
            unionId: $union?->getId(),
            commentId: $comment?->getCommentId() ?? $comment?->getId(),
            message: $command->message,
            photoIds: $this->getPhotoIds($command->photoIds),
            audioIds: $this->getAudioIds($command->audioIds),
            videoIds: $this->getVideoIds($command->videoIds),
            stickerId: $command->stickerId,
            uniqueTime: $command->uniqueTime
        );

        $this->postCommentRepository->add($postComment);

        $this->flusher->flush();

        $this->postUpdateCounterCommentsHandler->handle($post->getId());

        if ($commentId = $comment?->getId()) {
            $this->postCommentUpdateCounterCommentsHandler->handle($commentId);

            if (null === $comment->getUnionId() && $comment->getUserId() !== $user->getId()) {
                $this->postCommentAnsweredHandler->handle(
                    new PostCommentAnsweredCommand(
                        userId: $user->getId(),
                        commentId: $commentId
                    )
                );
            }
        }

        if (null === $post->getUnionId() && $post->getUserId() !== $user->getId()) {
            $this->postCommentedHandler->handle(
                new PostCommentedCommand(
                    userId: $user->getId(),
                    postId: $post->getId()
                )
            );
        }

        $this->postRealtimeNotifier->newComment(
            postId: $post->getId(),
            data: $this->postCommentUnifier->unifyOne(null, $postComment->toArray())
        );
    }

    private function checkLimits(int $userId): void
    {
        // Check max limit
        if ($this->postCommentRepository->countByUserId($userId) >= PostComment::limitTotal()) {
            throw new DomainExceptionModule(
                module: 'post',
                message: 'error.post_comment.limit_total',
                code: 2
            );
        }

        // Check daily limit
        if ($this->postCommentRepository->countTodayByUserId($userId) >= PostComment::limitDaily()) {
            throw new DomainExceptionModule(
                module: 'post',
                message: 'error.post_comment.limit_daily',
                code: 3
            );
        }
    }

    /** @return int[]|null */
    private function getPhotoIds(?array $arr): ?array
    {
        if (empty($arr)) {
            return null;
        }

        $items = [];

        /** @var array{id: int} $item */
        foreach ($this->photoGetByIdsFetcher->fetch(new PhotoGetByIdsQuery($arr)) as $item) {
            $items[] = $item['id'];
        }

        if (empty($items)) {
            return null;
        }

        return $items;
    }

    /** @return int[]|null */
    private function getAudioIds(?array $arr): ?array
    {
        if (empty($arr)) {
            return null;
        }

        $items = [];

        /** @var array{id: int} $item */
        foreach ($this->audioGetByIdsFetcher->fetch(new AudioGetByIdsQuery($arr)) as $item) {
            $items[] = $item['id'];
        }

        if (empty($items)) {
            return null;
        }

        return $items;
    }

    /** @return int[]|null */
    private function getVideoIds(?array $arr): ?array
    {
        if (empty($arr)) {
            return null;
        }

        $items = [];

        /** @var array{id: int} $item */
        foreach ($this->videoGetByIdsFetcher->fetch(new VideoGetByIdsQuery($arr)) as $item) {
            $items[] = $item['id'];
        }

        if (empty($items)) {
            return null;
        }

        return $items;
    }
}

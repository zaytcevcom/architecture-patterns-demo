<?php

declare(strict_types=1);

namespace App\Modules\Post\Entity\PostComment;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

class PostCommentRepository
{
    /** @var EntityRepository<PostComment> */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(PostComment::class);
        $this->em = $em;
    }

    public function countByUserId(int $userId): int
    {
        return $this->repo
            ->matching(
                Criteria::create()
                    ->andWhere(Criteria::expr()->eq('userId', $userId))
                    ->andWhere(Criteria::expr()->isNull('deletedAt'))
            )
            ->count();
    }

    public function countTodayByUserId(int $userId): int
    {
        $timeFrom = strtotime(date('Y-m-d'));

        return $this->repo
            ->matching(
                Criteria::create()
                    ->andWhere(Criteria::expr()->eq('userId', $userId))
                    ->andWhere(Criteria::expr()->gt('createdAt', $timeFrom))
                    ->andWhere(Criteria::expr()->isNull('deletedAt'))
            )
            ->count();
    }

    public function countByPostId(int $postId): int
    {
        return $this->repo
            ->matching(
                Criteria::create()
                    ->andWhere(Criteria::expr()->eq('postId', $postId))
                    ->andWhere(Criteria::expr()->isNull('deletedAt'))
            )
            ->count();
    }

    public function countByCommentId(int $commentId): int
    {
        return $this->repo
            ->matching(
                Criteria::create()
                    ->andWhere(Criteria::expr()->eq('commentId', $commentId))
                    ->andWhere(Criteria::expr()->isNull('deletedAt'))
            )
            ->count();
    }

    public function getById(int $id): PostComment
    {
        if (!$postComment = $this->findById($id)) {
            throw new DomainExceptionModule(
                module: 'post',
                message: 'error.post.post_comment_not_found',
                code: 1
            );
        }

        return $postComment;
    }

    public function findById(int $id): ?PostComment
    {
        return $this->repo->findOneBy([
            'id' => $id,
            'deletedAt' => null,
        ]);
    }

    public function findByUniqueTime(int $postId, int $uniqueTime, int $userId): ?PostComment
    {
        return $this->repo->findOneBy([
            'uniqueTime' => $uniqueTime,
            'postId' => $postId,
            'userId' => $userId,
            'deletedAt' => null,
        ]);
    }

    public function add(PostComment $postComment): void
    {
        $this->em->persist($postComment);
    }

    public function remove(PostComment $postComment): void
    {
        $this->em->remove($postComment);
    }
}

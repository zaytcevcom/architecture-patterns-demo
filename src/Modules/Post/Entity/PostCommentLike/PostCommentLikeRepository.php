<?php

declare(strict_types=1);

namespace App\Modules\Post\Entity\PostCommentLike;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class PostCommentLikeRepository
{
    /** @var EntityRepository<PostCommentLike> */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(PostCommentLike::class);
        $this->em = $em;
    }

    public function countByCommentId(int $commentId): int
    {
        return $this->repo
            ->matching(
                Criteria::create()
                    ->andWhere(Criteria::expr()->eq('commentId', $commentId))
            )
            ->count();
    }

    public function countByUserId(int $userId): int
    {
        return $this->repo
            ->matching(
                Criteria::create()
                    ->andWhere(Criteria::expr()->eq('userId', $userId))
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
            )
            ->count();
    }

    public function findByCommentAndUserIds(int $commentId, int $userId): ?PostCommentLike
    {
        return $this->repo->findOneBy([
            'commentId' => $commentId,
            'userId' => $userId,
        ]);
    }

    public function add(PostCommentLike $postCommentLike): void
    {
        $this->em->persist($postCommentLike);
    }

    public function remove(PostCommentLike $postCommentLike): void
    {
        $this->em->remove($postCommentLike);
    }
}

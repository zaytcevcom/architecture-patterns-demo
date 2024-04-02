<?php

declare(strict_types=1);

namespace App\Modules\Post\Entity\PostLike;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class PostLikeRepository
{
    /** @var EntityRepository<PostLike> */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(PostLike::class);
        $this->em = $em;
    }

    public function countByPost(int $postId): int
    {
        return $this->repo
            ->matching(
                Criteria::create()
                    ->andWhere(Criteria::expr()->eq('postId', $postId))
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

    public function findByPostAndUserIds(int $postId, int $userId): ?PostLike
    {
        return $this->repo->findOneBy([
            'postId' => $postId,
            'userId' => $userId,
        ]);
    }

    public function add(PostLike $postLike): void
    {
        $this->em->persist($postLike);
    }

    public function remove(PostLike $postLike): void
    {
        $this->em->remove($postLike);
    }
}

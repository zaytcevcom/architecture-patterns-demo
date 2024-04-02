<?php

declare(strict_types=1);

namespace App\Modules\Audio\Entity\AudioAlbumUser;

use App\Modules\Audio\Entity\AudioAlbum\AudioAlbum;
use App\Modules\Identity\Entity\User\User;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class AudioAlbumUserRepository
{
    /**
     * @var EntityRepository<AudioAlbumUser>
     */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(AudioAlbumUser::class);
        $this->em = $em;
    }

    public function countByAudioAlbum(AudioAlbum $audioAlbum): int
    {
        return $this->repo
            ->matching(
                Criteria::create()
                    ->andWhere(Criteria::expr()->eq('audioAlbum', $audioAlbum))
            )
            ->count();
    }

    public function durationByAudioAlbum(AudioAlbum $audioAlbum): int
    {
        $result = $this->em->getConnection()->createQueryBuilder()
            ->select('SUM(a.duration) AS duration')
            ->from('audios', 'a')
            ->andWhere('a.deleted_at IS NULL && a.hide = 0')
            ->andWhere('a.album_id = :albumId')
            ->setParameter('albumId', $audioAlbum->getId())
            ->setFirstResult(0)
            ->fetchAssociative();

        /** @var array{duration:string|null} $result */
        return (int)($result['duration'] ?? 0);
    }

    public function countLikesByAudioAlbum(AudioAlbum $audioAlbum): int
    {
        $result = $this->em->getConnection()->createQueryBuilder()
            ->select('SUM(a.count_add) as count_add')
            ->from('audios', 'a')
            ->andWhere('a.deleted_at IS NULL && a.hide = 0')
            ->andWhere('a.album_id = :albumId')
            ->setParameter('albumId', $audioAlbum->getId())
            ->setFirstResult(0)
            ->fetchAssociative();

        /** @var array{count_add:string|null} $result */
        return (int)($result['count_add'] ?? 0);
    }

    public function countByUserId(int $userId): int
    {
        $queryBuilder = $this->em->getConnection()->createQueryBuilder();

        $result = $queryBuilder
            ->select('COUNT(*) AS count')
            ->from('audios_albums_owners', 'aao')
            ->innerJoin('aao', 'audios_albums', 'aa', 'aao.album_id = aa.id')
            ->andWhere('aa.hide = 0 && aa.deleted_at IS NULL')
            ->andWhere('aao.owner_id = :userId')
            ->setParameter('userId', $userId)
            ->setFirstResult(0)
            ->fetchAssociative();

        /** @var array{count:int|null} $result */
        return $result['count'] ?? 0;
    }

    public function countTodayByUserId(int $userId): int
    {
        $timeFrom = strtotime(date('Y-m-d'));

        $queryBuilder = $this->em->getConnection()->createQueryBuilder();

        $result = $queryBuilder
            ->select('COUNT(*) AS count')
            ->from('audios_albums_owners', 'aao')
            ->innerJoin('aao', 'audios_albums', 'aa', 'aao.album_id = aa.id')
            ->andWhere('aa.hide = 0 && aa.deleted_at IS NULL')
            ->andWhere('aao.owner_id = :userId')
            ->andWhere('aao.time > :timeFrom')
            ->setParameter('userId', $userId)
            ->setParameter('timeFrom', $timeFrom)
            ->setFirstResult(0)
            ->fetchAssociative();

        /** @var array{count:int|null} $result */
        return $result['count'] ?? 0;
    }

    public function findByAudioAlbumAndUser(AudioAlbum $audioAlbum, User $user): ?AudioAlbumUser
    {
        return $this->repo->findOneBy([
            'audioAlbum' => $audioAlbum,
            'user' => $user,
        ]);
    }

    public function add(AudioAlbumUser $audioAlbumUser): void
    {
        $this->em->persist($audioAlbumUser);
    }

    public function remove(AudioAlbumUser $audioAlbumUser): void
    {
        $this->em->remove($audioAlbumUser);
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Audio\Entity\AudioAlbum;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

class AudioAlbumRepository
{
    /** @var EntityRepository<AudioAlbum> */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(AudioAlbum::class);
        $this->em = $em;
    }

    public function countLikesByUnionId(int $unionId): int
    {
        $result = $this->em->getConnection()->createQueryBuilder()
            ->select('SUM(aa.count_likes) AS count_likes')
            ->from('audio_album_union', 'aau')
            ->innerJoin('aau', 'audios_albums', 'aa', 'aau.album_id = aa.id')
            ->andWhere('aa.deleted_at IS NULL && aa.hide = 0')
            ->andWhere('aau.union_id = :unionId')
            ->setParameter('unionId', $unionId)
            ->setFirstResult(0)
            ->fetchAssociative();

        /** @var array{count_likes:string|null} $result */
        return (int)($result['count_likes'] ?? 0);
    }

    public function countAlbumsByUnionId(int $unionId): int
    {
        $result = $this->em->getConnection()->createQueryBuilder()
            ->select('COUNT(aa.id) AS count')
            ->from('audio_album_union', 'aau')
            ->innerJoin('aau', 'audios_albums', 'aa', 'aau.album_id = aa.id')
            ->andWhere('aa.deleted_at IS NULL && aa.hide = 0')
            ->andWhere('aau.union_id = :unionId')
            ->andWhere('aa.is_album = 1')
            ->setParameter('unionId', $unionId)
            ->setFirstResult(0)
            ->fetchAssociative();

        /** @var array{count:string|null} $result */
        return (int)($result['count'] ?? 0);
    }

    public function countSinglesByUnionId(int $unionId): int
    {
        $result = $this->em->getConnection()->createQueryBuilder()
            ->select('COUNT(aa.id) AS count')
            ->from('audio_album_union', 'aau')
            ->innerJoin('aau', 'audios_albums', 'aa', 'aau.album_id = aa.id')
            ->andWhere('aa.deleted_at IS NULL && aa.hide = 0')
            ->andWhere('aau.union_id = :unionId')
            ->andWhere('aa.is_album = 0')
            ->setParameter('unionId', $unionId)
            ->setFirstResult(0)
            ->fetchAssociative();

        /** @var array{count:string|null} $result */
        return (int)($result['count'] ?? 0);
    }

    public function getById(int $id): AudioAlbum
    {
        if (!$audioAlbum = $this->findById($id)) {
            throw new DomainExceptionModule(
                module: 'audio',
                message: 'error.audio.audio_album_not_found',
                code: 1
            );
        }

        return $audioAlbum;
    }

    public function findById(int $id): ?AudioAlbum
    {
        return $this->repo->findOneBy(['id' => $id]);
    }

    public function add(AudioAlbum $audioAlbum): void
    {
        $this->em->persist($audioAlbum);
    }

    public function remove(AudioAlbum $audioAlbum): void
    {
        $this->em->remove($audioAlbum);
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Audio\Entity\Audio;

use App\Modules\Audio\Entity\AudioAlbum\AudioAlbum;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

class AudioRepository
{
    /** @var EntityRepository<Audio> */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(Audio::class);
        $this->em = $em;
    }

    public function countByAudioAlbumId(int $audioAlbumId): int
    {
        $result = $this->em->getConnection()->createQueryBuilder()
            ->select('count(*) as count')
            ->from('audios', 'a')
            ->andWhere('a.deleted_at IS NULL && a.hide = 0')
            ->andWhere('a.album_id = :albumId')
            ->setParameter('albumId', $audioAlbumId)
            ->setFirstResult(0)
            ->fetchAssociative();

        /** @var array{count:string|null} $result */
        return (int)($result['count'] ?? 0);
    }

    public function countByUnionId(int $unionId): int
    {
        $result = $this->em->getConnection()->createQueryBuilder()
            ->select('count(a.id) as count')
            ->from('audio_union', 'au')
            ->innerJoin('au', 'audios', 'a', 'au.audio_id = a.id')
            ->andWhere('a.deleted_at IS NULL && a.hide = 0')
            ->andWhere('au.union_id = :unionId')
            ->setParameter('unionId', $unionId)
            ->setFirstResult(0)
            ->fetchAssociative();

        /** @var array{count:string|null} $result */
        return (int)($result['count'] ?? 0);
    }

    public function getById(int $id): Audio
    {
        if (!$audio = $this->findById($id)) {
            throw new DomainExceptionModule(
                module: 'audio',
                message: 'error.audio.audio_not_found',
                code: 1
            );
        }

        return $audio;
    }

    public function findById(int $id): ?Audio
    {
        return $this->repo->findOneBy(['id' => $id]);
    }

    public function findByUnionIdAndNameAndVersion(int $unionId, string $name, ?string $version): ?Audio
    {
        $name = trim($name);
        $version = trim($version ?? '');

        if (empty($version)) {
            $version = null;
        }

        $queryBuilder = $this->repo->createQueryBuilder('a');

        $queryBuilder
            ->innerJoin('a.audio_union', 'au')
            ->where('au.union_id = :unionId')
            ->andWhere('a.name = :name')
            ->andWhere('a.version = :version')
            ->setParameters([
                'unionId' => $unionId,
                'name' => $name,
                'version' => $version,
            ]);

        try {
            /** @var Audio */
            return $queryBuilder->getQuery()->getOneOrNullResult();
        } catch (Exception) {
            return null;
        }
    }

    public function getBySourceFileId(string $fileId): Audio
    {
        if (!$audio = $this->findBySourceFileId($fileId)) {
            throw new DomainExceptionModule(
                module: 'audio',
                message: 'error.audio.audio_not_found',
                code: 1
            );
        }

        return $audio;
    }

    public function findBySourceFileId(string $fileId): ?Audio
    {
        return $this->repo->findOneBy(['sourceFileId' => $fileId]);
    }

    public function markDeletedByAlbum(AudioAlbum $album): void
    {
        $time = time();

        $this->em->createQueryBuilder()
            ->update(Audio::class, 'a')
            ->set('a.hide', $time)
            ->set('a.deletedAt', $time)
            ->andWhere('a.album = :album')
            ->setParameter(':album', $album)
            ->getQuery()->execute();
    }

    public function add(Audio $audio): void
    {
        $this->em->persist($audio);
    }

    public function remove(Audio $audio): void
    {
        $this->em->remove($audio);
    }
}

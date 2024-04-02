<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\AudioPlaylist\GetById;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

final readonly class AudioPlaylistGetByIdFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /**
     * @throws Exception
     */
    public function fetch(AudioPlaylistGetByIdQuery $query): array
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $result = $queryBuilder
            ->select('a.*')
            ->from('audio_playlist', 'a')
            ->where('a.id = :id')
            ->andWhere('a.deleted_at IS NULL')
            ->setParameter('id', $query->id)
            ->executeQuery()
            ->fetchAssociative();

        if ($result === false) {
            throw new DomainExceptionModule(
                module: 'audio',
                message: 'error.audio.audio_playlist_not_found',
                code: 1
            );
        }

        return $result;
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Community\GetSections;

use App\Modules\Union\Entity\UnionSection\UnionSection;
use App\Modules\Union\Entity\UnionSection\UnionSectionRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use ZayMedia\Shared\Components\Flusher;

final readonly class CommunityGetSectionsFetcher
{
    public function __construct(
        private Connection $connection,
        private UnionSectionRepository $unionSectionRepository,
        private Flusher $flusher
    ) {}

    /**
     * @throws Exception
     */
    public function fetch(CommunityGetSectionsQuery $query): array
    {
        $result = $this->connection->createQueryBuilder()
            ->select(['posts', 'photos', 'videos', 'audios', 'contacts', 'links', 'messages'])
            ->from('union_section')
            ->andWhere('union_id = :unionId')
            ->setParameter('unionId', $query->unionId)
            ->setFirstResult(0)
            ->fetchAllAssociative();

        if (\count($result) === 0) {
            return $this->setSections($query->unionId);
        }

        return [
            'posts' => (bool)$result[0]['posts'],
            'photos' => (bool)$result[0]['photos'],
            'videos' => (bool)$result[0]['videos'],
            'audios' => (bool)$result[0]['audios'],
            'contacts' => (bool)$result[0]['contacts'],
            'links' => (bool)$result[0]['links'],
            'messages' => (bool)$result[0]['messages'],
        ];
    }

    private function setSections(int $unionId): array
    {
        $sections = UnionSection::create($unionId);
        $this->unionSectionRepository->add($sections);
        $this->flusher->flush();

        return [
            'posts' => $sections->isPosts(),
            'photos' => $sections->isPhotos(),
            'videos' => $sections->isVideos(),
            'audios' => $sections->isAudios(),
            'contacts' => $sections->isContacts(),
            'links' => $sections->isLinks(),
            'messages' => $sections->isMessages(),
        ];
    }
}

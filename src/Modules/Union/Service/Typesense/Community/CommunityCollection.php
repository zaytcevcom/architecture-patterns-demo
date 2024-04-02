<?php

declare(strict_types=1);

namespace App\Modules\Union\Service\Typesense\Community;

use Exception;
use Throwable;
use Typesense\Client;
use ZayMedia\Shared\Helpers\Helper;

class CommunityCollection
{
    private const COLLECTION_NAME = 'community';

    public function __construct(
        private readonly Client $client
    ) {}

    /** @throws Exception */
    public function createSchema(): void
    {
        try {
            $schema = [
                'name'      => self::COLLECTION_NAME,
                'fields'    => [
                    [
                        'name'  => 'identifier',
                        'type'  => 'int64',
                    ],
                    [
                        'name'  => 'title',
                        'type'  => 'string',
                    ],
                    [
                        'name'      => 'sphere_id',
                        'type'      => 'int32',
                        'optional'  => true,
                    ],
                    [
                        'name'      => 'category_id',
                        'type'      => 'int32',
                        'optional'  => true,
                    ],
                    [
                        'name'      => 'category_kind',
                        'type'      => 'int32',
                        'optional'  => true,
                    ],
                    [
                        'name'      => 'city_id',
                        'type'      => 'int32',
                        'optional'  => true,
                    ],
                    [
                        'name'  => 'count_members',
                        'type'  => 'int32',
                    ],
                ],
                'default_sorting_field' => 'identifier',
            ];

            $this->client->collections->create($schema);
        } catch (Throwable $throwable) {
            throw new Exception($throwable->getMessage());
        }
    }

    /**
     * @param CommunityDocument[] $documents
     * @throws Exception
     */
    public function upsertDocuments(array $documents): void
    {
        $data = [];

        foreach ($documents as $document) {
            $data[] = [
                'identifier' => $document->identifier,
                'title' => $document->title,
                'sphere_id' => $document->sphereId,
                'category_id' => $document->categoryId,
                'category_kind' => $document->categoryKind,
                'city_id' => $document->cityId,
                'count_members' => $document->countMembers,
            ];
        }

        try {
            $this->client->collections[self::COLLECTION_NAME]->documents->import($data);
        } catch (Throwable $throwable) {
            throw new Exception($throwable->getMessage());
        }
    }

    /** @throws Exception */
    public function deleteSchema(): void
    {
        try {
            $this->client->collections[self::COLLECTION_NAME]->delete();
        } catch (Throwable $throwable) {
            throw new Exception($throwable->getMessage());
        }
    }

    /**
     * @return int[]
     * @throws Exception
     */
    public function searchIdentifiers(CommunityQuery $query): array
    {
        $filter = [];

        if (null !== $query->sphereId) {
            $filter[] = 'sphere_id: ' . $query->sphereId;
        }

        if (null !== $query->categoryId) {
            $filter[] = 'category_id: ' . $query->categoryId;
        }

        if (null !== $query->categoryKind) {
            $filter[] = 'category_kind: ' . $query->categoryKind;
        }

        try {
            $filterBy = (!empty($filter)) ? implode(' && ', $filter) : null;

            /** @var array{hits: array{document: array{identifier: int}}[]} $result */
            $result = $this->client->collections[self::COLLECTION_NAME]->documents->search([
                'query_by'  => 'title',
                'q'         => Helper::ucFirst(trim($query->search)),
                'filter_by' => $filterBy,
                'limit'     => $query->limit,
                'sort_by'   => '_text_match:desc,count_members:desc',
                'use_cache' => true,
                'cache_ttl' => 10 * 60,
            ]);

            $ids = [];

            foreach ($result['hits'] as $hit) {
                $ids[] = $hit['document']['identifier'];
            }
            return $ids;
        } catch (Throwable $throwable) {
            throw new Exception($throwable->getMessage());
        }
    }
}

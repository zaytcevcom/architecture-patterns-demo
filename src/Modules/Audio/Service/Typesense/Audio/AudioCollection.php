<?php

declare(strict_types=1);

namespace App\Modules\Audio\Service\Typesense\Audio;

use Exception;
use Throwable;
use Typesense\Client;
use ZayMedia\Shared\Helpers\Helper;

class AudioCollection
{
    private const COLLECTION_NAME = 'audio';

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
                        'name'  => 'union_ids',
                        'type'  => 'int64[]',
                    ],
                    [
                        'name'  => 'title',
                        'type'  => 'string',
                    ],
                    [
                        'name'  => 'artists',
                        'type'  => 'string[]',
                    ],
                    [
                        'name'  => 'year',
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
     * @param AudioDocument[] $documents
     * @throws Exception
     */
    public function upsertDocuments(array $documents): void
    {
        $data = [];

        foreach ($documents as $document) {
            $data[] = [
                'identifier' => $document->identifier,
                'union_ids' => $document->unionIds,
                'title' => $document->title,
                'artists' => $document->artists,
                'year' => $document->year,
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
    public function searchIdentifiers(AudioQuery $query): array
    {
        try {
            /** @var array{hits: array{document: array{identifier: int}}[]} $result */
            $result = $this->client->collections[self::COLLECTION_NAME]->documents->search([
                'query_by'  => 'title,artists',
                'q'         => Helper::ucFirst(trim($query->search)),
                'filter_by' => null,
                'limit'     => $query->limit,
                'sort_by'   => '_text_match:desc,year:desc',
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

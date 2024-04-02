<?php

declare(strict_types=1);

namespace App\Modules\Identity\Service\Typesense\User;

use Exception;
use Throwable;
use Typesense\Client;
use ZayMedia\Shared\Helpers\Helper;

class UserCollection
{
    private const COLLECTION_NAME = 'user';

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
                        'name'      => 'country_id',
                        'type'      => 'int32',
                        'optional'  => true,
                    ],
                    [
                        'name'      => 'city_id',
                        'type'      => 'int32',
                        'optional'  => true,
                    ],
                    [
                        'name'      => 'marital',
                        'type'      => 'int32',
                        'optional'  => true,
                    ],
                    [
                        'name'      => 'sex',
                        'type'      => 'int32',
                        'optional'  => true,
                    ],
                    [
                        'name'      => 'birthday_year',
                        'type'      => 'int32',
                        'optional'  => true,
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
     * @param UserDocument[] $documents
     * @throws Exception
     */
    public function upsertDocuments(array $documents): void
    {
        $data = [];

        foreach ($documents as $document) {
            $data[] = [
                'identifier' => $document->identifier,
                'title' => $document->title,
                'country_id' => $document->countryId,
                'city_id' => $document->cityId,
                'marital' => $document->marital,
                'sex' => $document->sex,
                'birthday_year' => $document->birthdayYear,
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
    public function searchIdentifiers(UserQuery $query): array
    {
        $filter = [];

        if (null !== $query->countryId) {
            $filter[] = 'country_id: ' . $query->countryId;
        }

        if (null !== $query->cityId) {
            $filter[] = 'city_id: ' . $query->cityId;
        }

        if (null !== $query->marital) {
            $filter[] = 'marital: ' . $query->marital;
        }

        if (null !== $query->sex) {
            $filter[] = 'sex: ' . $query->sex;
        }

        try {
            $filterBy = (!empty($filter)) ? implode(' && ', $filter) : null;

            /** @var array{hits: array{document: array{identifier: int}}[]} $result */
            $result = $this->client->collections[self::COLLECTION_NAME]->documents->search([
                'query_by'  => 'title',
                'q'         => Helper::ucFirst(trim($query->search)),
                'filter_by' => $filterBy,
                'limit'     => $query->limit,
                'sort_by'   => '_text_match:desc,identifier:asc',
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

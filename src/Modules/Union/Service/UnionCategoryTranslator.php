<?php

declare(strict_types=1);

namespace App\Modules\Union\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class UnionCategoryTranslator
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /** @throws Exception */
    public function translate(array $rows, string $locale): array
    {
        /** @var array{array{name: string}} $translations */
        $translations = $this->getTranslations($locale);

        /** @var array{array{id: int, name: string}} $rows */
        foreach ($rows as $key => $row) {
            if (isset($translations[$row['id']])) {
                $rows[$key]['name'] = $translations[$row['id']]['name'];
            }
        }

        return $rows;
    }

    /** @throws Exception */
    private function getTranslations(string $locale): array
    {
        $translates = [];

        $modelsTranslate = $this->connection->createQueryBuilder()
            ->select(['*'])
            ->from('unions_categories_translate')
            ->where('lang = :lang')
            ->setParameter('lang', $locale)
            ->executeQuery()
            ->fetchAllAssociative();

        /** @var array{category_id: int, value: string} $modelTranslate */
        foreach ($modelsTranslate as $modelTranslate) {
            if (!isset($translates[$modelTranslate['category_id']])) {
                $translates[$modelTranslate['category_id']] = [
                    'name' => $modelTranslate['value'],
                ];
            }
        }

        return $translates;
    }
}

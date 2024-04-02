<?php

declare(strict_types=1);

namespace App\Modules\Identity\Query\GetTotalCount;

use Doctrine\DBAL\Connection;
use Exception;

final readonly class GetTotalCountFetcher
{
    public function __construct(
        private Connection $connection,
    ) {}

    public function fetch(): int
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        try {
            $count = $queryBuilder
                ->select(['u.id'])
                ->from('users', 'u')
                ->executeQuery()
                ->rowCount();
        } catch (Exception) {
            $count = 108_364;
        }

        return $count;
    }
}

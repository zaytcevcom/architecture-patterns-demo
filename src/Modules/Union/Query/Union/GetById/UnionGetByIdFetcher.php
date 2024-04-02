<?php

declare(strict_types=1);

namespace App\Modules\Union\Query\Union\GetById;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

final readonly class UnionGetByIdFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /**
     * @throws Exception
     */
    public function fetch(UnionGetByIdQuery $query): array
    {
        $sqlQuery = $this->connection->createQueryBuilder()
            ->select(['*'])
            ->from('unions')
            ->andWhere('id = :id')
            ->setParameter('id', $query->id);

        $result = $sqlQuery->executeQuery()->fetchAssociative();

        if ($result === false) {
            throw new DomainExceptionModule(
                module: 'union',
                message: 'error.union.union_not_found',
                code: 1
            );
        }

        return $result;
    }
}

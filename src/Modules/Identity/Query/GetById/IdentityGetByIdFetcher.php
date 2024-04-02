<?php

declare(strict_types=1);

namespace App\Modules\Identity\Query\GetById;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

final readonly class IdentityGetByIdFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /**
     * @throws Exception
     */
    public function fetch(IdentityGetByIdQuery $query): array
    {
        $sqlQuery = $this->connection->createQueryBuilder()
            ->select(['*'])
            ->from('users')
            ->andWhere('id = :id')
            ->setParameter('id', $query->id);

        $result = $sqlQuery->executeQuery()->fetchAssociative();

        if ($result === false) {
            throw new DomainExceptionModule(
                module: 'identity',
                message: 'error.user.user_not_found',
                code: 1
            );
        }

        return $result;
    }
}

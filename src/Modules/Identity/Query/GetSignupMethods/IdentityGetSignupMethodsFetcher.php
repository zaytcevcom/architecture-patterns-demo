<?php

declare(strict_types=1);

namespace App\Modules\Identity\Query\GetSignupMethods;

use Doctrine\DBAL\Connection;

final readonly class IdentityGetSignupMethodsFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    public function fetch(): ?array
    {
        $result = $this->connection->createQueryBuilder()
            ->select(['name', 'status'])
            ->from('identity_signup_methods')
            ->executeQuery();

        /** @var array{name: string, status: int}|false */
        $rows = $result->fetchAllAssociative();

        if ($rows === false) {
            return null;
        }

        return $rows;
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Identity\Query\FindIdByCredentials;

use App\Components\PhoneHelper;
use App\Modules\Identity\Service\OldPasswordHasher;
use App\Modules\Identity\Service\PasswordHasher;
use Doctrine\DBAL\Connection;

final readonly class Fetcher
{
    public function __construct(
        private Connection $connection,
        private PasswordHasher $passwordHasher,
        private OldPasswordHasher $oldPasswordHasher
    ) {}

    public function fetch(Query $query): ?User
    {
        $username = mb_strtolower($query->username);

        if (!str_contains($username, '@')) {
            $username = PhoneHelper::cleaner($username);
        }

        $result = $this->connection->createQueryBuilder()
            ->select([
                'id',
                'password',
            ])
            ->from('users')
            ->where('(email = :username || phone = :username) && deleted IS NULL && blocked IS NULL')
            ->setParameter('username', $username)
            ->executeQuery();

        /** @var array{id: int, password: ?string}|false */
        $row = $result->fetchAssociative();

        if ($row === false) {
            return null;
        }

        $hash = $row['password'];

        if ($hash === null) {
            return null;
        }

        if (
            !$this->passwordHasher->validate($query->password, $hash) &&
            !$this->oldPasswordHasher->validate($query->password, $hash)
        ) {
            return null;
        }

        return new User(
            id: $row['id'],
            isActive: true// $row['status'] === Status::ACTIVE
        );
    }
}

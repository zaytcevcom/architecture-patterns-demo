<?php

declare(strict_types=1);

namespace App\Modules\OAuth\Entity;

use App\Modules\Identity\Query\FindIdByCredentials\Fetcher;
use App\Modules\Identity\Query\FindIdByCredentials\Query;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

final class UserRepository implements UserRepositoryInterface
{
    private Fetcher $users;

    public function __construct(Fetcher $users)
    {
        $this->users = $users;
    }

    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ): ?User {
        $identity = $this->users->fetch(
            new Query($username, $password)
        );

        if ($identity === null) {
            throw new OAuthServerException('error.incorrect_credentials', 1, 'invalid_user', 401);
        }

        return new User((string)$identity->id);
    }
}

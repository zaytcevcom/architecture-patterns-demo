<?php

declare(strict_types=1);

namespace App\Modules\OAuth\Entity;

use App\Modules\Identity\Query\FindIdentityById\Fetcher;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

final class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    private Fetcher $users;

    public function __construct(Fetcher $users)
    {
        $this->users = $users;
    }

    public function getNewToken(
        ClientEntityInterface $clientEntity,
        array $scopes,
        $userIdentifier = null
    ): AccessToken {
        $accessToken = new AccessToken($clientEntity, $scopes);

        if ($userIdentifier !== null) {
            $identity = $this->users->fetch((string)$userIdentifier);

            if ($identity === null) {
                throw new OAuthServerException('error.incorrect_credentials', 1, 'invalid_user', 401);
            }

            $accessToken->setUserIdentifier($identity->id);
            //            $accessToken->setUserRole($identity->role);
        }

        return $accessToken;
    }

    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
        // do nothing
    }

    public function revokeAccessToken($tokenId): void
    {
        // do nothing
    }

    public function isAccessTokenRevoked($tokenId): bool
    {
        return false;
    }
}

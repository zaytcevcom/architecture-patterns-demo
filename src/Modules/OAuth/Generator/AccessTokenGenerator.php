<?php

declare(strict_types=1);

namespace App\Modules\OAuth\Generator;

use App\Modules\OAuth\Entity\AccessToken;
use Exception;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use Ramsey\Uuid\Uuid;

final class AccessTokenGenerator
{
    private string $privateKeyPath;
    private ?AccessToken $accessToken = null;

    public function __construct(string $privateKeyPath)
    {
        $this->privateKeyPath = $privateKeyPath;
    }

    /**
     * @param ScopeEntityInterface[] $scopes
     * @throws Exception
     */
    public function generate(ClientEntityInterface $client, array $scopes, AccessTokenParams $params): AccessToken
    {
        $accessToken = new AccessToken($client, $scopes);

        $accessToken->setIdentifier(Uuid::uuid4()->toString());
        $accessToken->setExpiryDateTime($params->expires);
        $accessToken->setUserIdentifier($params->userId);
        $accessToken->setUserRole($params->role);

        $accessToken->setPrivateKey(new CryptKey($this->privateKeyPath, null, false));

        $this->accessToken = $accessToken;

        return $accessToken;
    }

    public function toJWTString(): ?string
    {
        return $this->accessToken?->__toString();
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\OAuth\Generator;

use App\Modules\OAuth\Entity\AccessToken;
use App\Modules\OAuth\Entity\RefreshToken;
use App\Modules\OAuth\Entity\RefreshTokenRepository;
use Defuse\Crypto\Crypto;
use Exception;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use Ramsey\Uuid\Uuid;

final class RefreshTokenGenerator
{
    private ?AccessToken $accessToken = null;
    private ?RefreshToken $refreshToken = null;
    private RefreshTokenRepository $refreshTokenRepository;

    public function __construct(RefreshTokenRepository $refreshTokenRepository)
    {
        $this->refreshTokenRepository = $refreshTokenRepository;
    }

    public function generate(AccessToken $accessToken, RefreshTokenParams $params): RefreshToken
    {
        $refreshToken = new RefreshToken();

        $refreshToken->setIdentifier(Uuid::uuid4()->toString());
        $refreshToken->setExpiryDateTime($params->expires);
        $refreshToken->setAccessToken($accessToken);

        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;

        $this->refreshTokenRepository->persistNewRefreshToken($refreshToken);

        return $refreshToken;
    }

    public function toJWTString(ClientEntityInterface $client, string $keyPath): ?string
    {
        if ($this->accessToken === null || $this->refreshToken === null) {
            return null;
        }

        $payload = [
            'client_id' => $client->getIdentifier(),
            'refresh_token_id' => $this->refreshToken->getIdentifier(),
            'access_token_id' => $this->accessToken->getIdentifier(),
            'scopes' => $this->accessToken->getScopes(),
            'user_id' => $this->accessToken->getUserIdentifier(),
            'expire_time' => $this->refreshToken->getExpiryDateTime()->getTimestamp(),
        ];

        try {
            $token = Crypto::encryptWithPassword(json_encode($payload), $keyPath);
        } catch (Exception) {
            $token = null;
        }

        return $token;
    }
}

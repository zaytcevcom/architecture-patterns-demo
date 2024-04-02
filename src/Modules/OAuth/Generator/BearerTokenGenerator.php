<?php

declare(strict_types=1);

namespace App\Modules\OAuth\Generator;

use App\Modules\OAuth\Entity\Client;
use DateTimeImmutable;
use Exception;
use League\OAuth2\Server\Entities\ScopeEntityInterface;

class BearerTokenGenerator
{
    private string $encryptionKeyPath;
    private AccessTokenGenerator $accessTokenGenerator;
    private RefreshTokenGenerator $refreshTokenGenerator;

    public function __construct(
        AccessTokenGenerator $accessTokenGenerator,
        RefreshTokenGenerator $refreshTokenGenerator,
        string $encryptionKeyPath
    ) {
        $this->encryptionKeyPath = $encryptionKeyPath;
        $this->accessTokenGenerator = $accessTokenGenerator;
        $this->refreshTokenGenerator = $refreshTokenGenerator;
    }

    /**
     * @param array<ScopeEntityInterface> $scopes
     * @throws Exception
     */
    public function generate(
        Client $client,
        string $userId,
        array $scopes = [],
        int $accessTokenExpiresIn = 10 * 60,
        int $refreshTokenExpiresIn = 7 * 24 * 60 * 60
    ): array {
        $accessToken = $this->accessTokenGenerator
            ->generate(
                $client,
                $scopes,
                new AccessTokenParams(
                    userId: $userId,
                    role: 'none',
                    expires: new DateTimeImmutable('+' . $accessTokenExpiresIn . ' minute'),
                )
            );

        $this->refreshTokenGenerator
            ->generate(
                $accessToken,
                new RefreshTokenParams(
                    expires: new DateTimeImmutable('+' . $refreshTokenExpiresIn . ' minute'),
                )
            );

        return [
            'token_type' => 'Bearer',
            'expires_in' => $accessTokenExpiresIn,
            'access_token' => $this->accessTokenGenerator->toJWTString(),
            'refresh_token' => $this->refreshTokenGenerator->toJWTString($client, $this->encryptionKeyPath),
        ];
    }
}

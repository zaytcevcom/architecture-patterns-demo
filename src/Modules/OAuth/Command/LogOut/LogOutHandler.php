<?php

declare(strict_types=1);

namespace App\Modules\OAuth\Command\LogOut;

use App\Modules\OAuth\Entity\RefreshTokenRepository;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use ZayMedia\Shared\Components\JWTParser;

final readonly class LogOutHandler
{
    public function __construct(
        private RefreshTokenRepository $refreshTokenRepository,
        private JWTParser $JWTParser
    ) {}

    /**
     * @throws EnvironmentIsBrokenException
     * @throws WrongKeyOrModifiedCiphertextException
     */
    public function handle(LogOutCommand $command): void
    {
        $jwtRefreshToken = $this->JWTParser->parseRefreshToken($command->refreshToken);

        /** @var array{refresh_token_id: string|null} $jwtRefreshToken */
        $this->refreshTokenRepository->revokeRefreshTokenAndAllSamePushTokens($jwtRefreshToken['refresh_token_id'] ?? '');
    }
}

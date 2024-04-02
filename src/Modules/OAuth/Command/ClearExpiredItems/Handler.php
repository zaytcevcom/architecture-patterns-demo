<?php

declare(strict_types=1);

namespace App\Modules\OAuth\Command\ClearExpiredItems;

use App\Modules\OAuth\Entity\AuthCodeRepository;
use App\Modules\OAuth\Entity\RefreshTokenRepository;
use DateTimeImmutable;

final readonly class Handler
{
    public function __construct(
        private AuthCodeRepository $authCodes,
        private RefreshTokenRepository $refreshTokens
    ) {}

    public function handle(Command $command): void
    {
        $date = new DateTimeImmutable($command->date);

        $this->authCodes->removeAllExpired($date);
        $this->refreshTokens->removeAllExpired($date);
    }
}

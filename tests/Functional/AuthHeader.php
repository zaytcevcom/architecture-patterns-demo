<?php

declare(strict_types=1);

namespace Test\Functional;

use App\Modules\OAuth\Entity\Client;
use App\Modules\OAuth\Entity\Scope;
use App\Modules\OAuth\Generator\AccessTokenGenerator;
use App\Modules\OAuth\Generator\AccessTokenParams;
use DateTimeImmutable;

use function App\Components\env;

final class AuthHeader
{
    public static function for(string $userId, string $role): string
    {
        $generator = new AccessTokenGenerator(env('JWT_PRIVATE_KEY_PATH'));

        $token = $generator->generate(
            new Client(
                identifier: 'frontend',
                name: 'LO',
                redirectUri: 'http://localhost/oauth'
            ),
            [new Scope('common')],
            new AccessTokenParams(
                userId: $userId,
                role: $role,
                expires: new DateTimeImmutable('+1000 years'),
            )
        );

        return 'Bearer ' . $token;
    }
}

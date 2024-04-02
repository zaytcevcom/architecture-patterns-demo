<?php

declare(strict_types=1);

namespace App\Modules\Identity\Service;

use Webmozart\Assert\Assert;

final class PasswordHasher
{
    private int $memoryCost;

    public function __construct(int $memoryCost = PASSWORD_ARGON2_DEFAULT_MEMORY_COST)
    {
        $this->memoryCost = $memoryCost;
    }

    public function hash(string $password): string
    {
        Assert::notEmpty($password);

        return password_hash($password, PASSWORD_ARGON2I, ['memory_cost' => $this->memoryCost]);
    }

    public function validate(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}

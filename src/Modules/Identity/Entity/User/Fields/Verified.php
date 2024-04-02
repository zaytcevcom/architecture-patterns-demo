<?php

declare(strict_types=1);

namespace App\Modules\Identity\Entity\User\Fields;

use Webmozart\Assert\Assert;

final class Verified
{
    private const NOT_VERIFIED = 0;
    private const VERIFIED = 1;
    private const OFFICIAL = 2;

    private int $value;

    public function __construct(int $value)
    {
        Assert::oneOf($value, [
            self::NOT_VERIFIED,
            self::VERIFIED,
            self::OFFICIAL,
        ]);
        $this->value = $value;
    }

    public function __toString(): string
    {
        return (string)$this->getValue();
    }

    public static function notVerified(): self
    {
        return new self(self::NOT_VERIFIED);
    }

    public static function verified(): self
    {
        return new self(self::VERIFIED);
    }

    public static function official(): self
    {
        return new self(self::OFFICIAL);
    }

    public function getValue(): int
    {
        return $this->value;
    }
}

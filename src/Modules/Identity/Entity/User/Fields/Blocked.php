<?php

declare(strict_types=1);

namespace App\Modules\Identity\Entity\User\Fields;

use Webmozart\Assert\Assert;

final class Blocked
{
    private const NOT_BLOCKED = null;
    private const BLOCKED = 1;

    private ?int $value;

    public function __construct(?int $value)
    {
        Assert::oneOf($value, [
            self::NOT_BLOCKED,
            self::BLOCKED,
        ]);
        $this->value = $value;
    }

    public function __toString(): string
    {
        return (string)$this->getValue();
    }

    public static function notBlocked(): self
    {
        return new self(self::NOT_BLOCKED);
    }

    public static function blocked(): self
    {
        return new self(self::BLOCKED);
    }

    public function getValue(): ?int
    {
        return $this->value;
    }
}

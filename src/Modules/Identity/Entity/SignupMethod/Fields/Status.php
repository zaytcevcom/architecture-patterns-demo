<?php

declare(strict_types=1);

namespace App\Modules\Identity\Entity\SignupMethod\Fields;

use Webmozart\Assert\Assert;

final class Status
{
    private const ACTIVE = 1;
    private const INACTIVE = 0;

    private int $value;

    public function __construct(string $value)
    {
        $value = (int)$value;

        Assert::oneOf($value, [
            self::ACTIVE,
            self::INACTIVE,
        ]);
        $this->value = $value;
    }

    public function __toString(): string
    {
        return (string)$this->getValue();
    }

    public static function active(): self
    {
        return new self((string)self::ACTIVE);
    }

    public static function inactive(): self
    {
        return new self((string)self::INACTIVE);
    }

    public function getValue(): int
    {
        return $this->value;
    }
}

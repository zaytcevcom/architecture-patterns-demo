<?php

declare(strict_types=1);

namespace App\Modules\Identity\Entity\User\Fields;

use Webmozart\Assert\Assert;

final class Deactivated
{
    private const NOT_DEACTIVATED = 0;
    private const DEACTIVATED = 1;

    private int $value;

    public function __construct(int $value)
    {
        Assert::oneOf($value, [
            self::NOT_DEACTIVATED,
            self::DEACTIVATED,
        ]);
        $this->value = $value;
    }

    public function __toString(): string
    {
        return (string)$this->getValue();
    }

    public static function notDeactivated(): self
    {
        return new self(self::NOT_DEACTIVATED);
    }

    public static function deactivated(): self
    {
        return new self(self::DEACTIVATED);
    }

    public function getValue(): int
    {
        return $this->value;
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Identity\Entity\User\Fields;

use Webmozart\Assert\Assert;

final class Sex
{
    private const UNKNOWN = null;
    private const MALE = 1;
    private const FEMALE = 0;

    private ?int $value;

    public function __construct(?int $value)
    {
        Assert::oneOf($value, [
            self::UNKNOWN,
            self::MALE,
            self::FEMALE,
            2, // todo
        ]);
        $this->value = $value;
    }

    public function __toString(): string
    {
        return (string)$this->getValue();
    }

    public static function male(): self
    {
        return new self(self::MALE);
    }

    public static function female(): self
    {
        return new self(self::FEMALE);
    }

    public function getValue(): ?int
    {
        return $this->value;
    }
}

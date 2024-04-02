<?php

declare(strict_types=1);

namespace App\Modules\Identity\Entity\User\Fields;

use Webmozart\Assert\Assert;

final class Marital
{
    private const NOT_SET = null;
    private const FREE = 0;
    private const NOT_MARRIED = 1;
    private const IN_LOVE = 2;
    private const MARRIED = 3;

    private ?int $value;

    public function __construct(?int $value)
    {
        //        Assert::oneOf($value, [
        //            self::NOT_SET,
        //            self::FREE,
        //            self::NOT_MARRIED,
        //            self::IN_LOVE,
        //            self::MARRIED,
        //        ]);
        $this->value = $value;
    }

    public function __toString(): string
    {
        return (string)$this->getValue();
    }

    public static function notSet(): self
    {
        return new self(self::NOT_SET);
    }

    public static function free(): self
    {
        return new self(self::FREE);
    }

    public static function notMarried(): self
    {
        return new self(self::NOT_MARRIED);
    }

    public static function inLove(): self
    {
        return new self(self::IN_LOVE);
    }

    public static function married(): self
    {
        return new self(self::MARRIED);
    }

    public function getValue(): ?int
    {
        return $this->value;
    }
}

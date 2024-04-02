<?php

declare(strict_types=1);

namespace App\Modules\Identity\Entity\User\Fields;

final class Deleted
{
    private const NOT_DELETED = null;

    private ?int $value;

    public function __construct(?int $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return (string)$this->getValue();
    }

    public static function notDeleted(): self
    {
        return new self(self::NOT_DELETED);
    }

    public function getValue(): ?int
    {
        return $this->value;
    }
}

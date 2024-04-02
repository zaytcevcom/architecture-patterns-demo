<?php

declare(strict_types=1);

namespace App\Modules\Identity\Entity\User\Fields;

use Webmozart\Assert\Assert;

final class ScreenName
{
    private string $value;

    public function __construct(string $value)
    {
        Assert::notEmpty($value);

        // todo: Проверка на кол-во символов и т.д.

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}

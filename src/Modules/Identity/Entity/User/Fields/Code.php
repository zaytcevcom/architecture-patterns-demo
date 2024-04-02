<?php

declare(strict_types=1);

namespace App\Modules\Identity\Entity\User\Fields;

use Webmozart\Assert\Assert;

final class Code
{
    private string $value;

    public function __construct(string $value)
    {
        Assert::notEmpty($value);

        // todo: Проверка кода

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}

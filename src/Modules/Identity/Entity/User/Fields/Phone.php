<?php

declare(strict_types=1);

namespace App\Modules\Identity\Entity\User\Fields;

use App\Components\PhoneHelper;
use Webmozart\Assert\Assert;

final class Phone
{
    private string $value;

    public function __construct(string $value)
    {
        Assert::notEmpty($value);

        // $value = trim($value, '+');
        $value = PhoneHelper::cleaner($value);

        // todo: Проверка номера телефона

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}

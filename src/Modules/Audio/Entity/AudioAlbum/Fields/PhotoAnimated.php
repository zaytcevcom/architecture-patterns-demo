<?php

declare(strict_types=1);

namespace App\Modules\Audio\Entity\AudioAlbum\Fields;

use Webmozart\Assert\Assert;

final class PhotoAnimated
{
    private string $value;

    public function __construct(string $value)
    {
        Assert::notEmpty($value);

        // todo: Проверка фото

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}

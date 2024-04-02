<?php

declare(strict_types=1);

namespace App\Modules\Identity\Entity\User\Fields;

use Webmozart\Assert\Assert;

final class PhotoFileId
{
    private ?string $value;

    public function __construct(?string $value)
    {
        // Assert::notEmpty($value);

        // todo: Проверка фото

        if (empty($value)) {
            $value = null;
        }

        $this->value = $value;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }
}

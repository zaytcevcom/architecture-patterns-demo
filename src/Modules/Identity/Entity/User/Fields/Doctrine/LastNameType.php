<?php

declare(strict_types=1);

namespace App\Modules\Identity\Entity\User\Fields\Doctrine;

use App\Modules\Identity\Entity\User\Fields\LastName;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class LastNameType extends StringType
{
    public const NAME = 'user_lastname';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof LastName) {
            return $value->getValue();
        }

        return (null !== $value) ? (string)$value : null;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?LastName
    {
        return !empty($value) ? new LastName((string)$value) : null;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}

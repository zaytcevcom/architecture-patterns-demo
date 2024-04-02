<?php

declare(strict_types=1);

namespace App\Modules\Identity\Entity\User\Fields\Doctrine;

use App\Modules\Identity\Entity\User\Fields\Sex;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\IntegerType;

final class SexType extends IntegerType
{
    public const NAME = 'user_sex';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?int
    {
        return $value instanceof Sex ? $value->getValue() : (int)$value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Sex
    {
        return null !== $value ? new Sex((int)$value) : Sex::male();
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

<?php

declare(strict_types=1);

namespace App\Modules\Identity\Entity\User\Fields\Doctrine;

use App\Modules\Identity\Entity\User\Fields\Marital;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\IntegerType;

final class MaritalType extends IntegerType
{
    public const NAME = 'user_marital';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?int
    {
        return $value instanceof Marital ? $value->getValue() : (int)$value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Marital
    {
        return null !== $value ? new Marital((int)$value) : null;
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

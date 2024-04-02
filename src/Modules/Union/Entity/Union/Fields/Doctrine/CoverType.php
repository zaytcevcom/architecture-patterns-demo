<?php

declare(strict_types=1);

namespace App\Modules\Union\Entity\Union\Fields\Doctrine;

use App\Modules\Union\Entity\Union\Fields\Cover;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonType;

final class CoverType extends JsonType
{
    public const NAME = 'union_cover';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof Cover) {
            return $value->getValue();
        }

        return (null !== $value) ? (string)$value : null;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Cover
    {
        return !empty($value) ? new Cover((string)$value) : null;
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

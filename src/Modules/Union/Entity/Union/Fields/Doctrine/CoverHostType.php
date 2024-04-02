<?php

declare(strict_types=1);

namespace App\Modules\Union\Entity\Union\Fields\Doctrine;

use App\Modules\Union\Entity\Union\Fields\CoverHost;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class CoverHostType extends StringType
{
    public const NAME = 'union_cover_host';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof CoverHost) {
            return $value->getValue();
        }

        return (null !== $value) ? (string)$value : null;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?CoverHost
    {
        return !empty($value) ? new CoverHost((string)$value) : null;
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

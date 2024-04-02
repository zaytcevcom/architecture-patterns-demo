<?php

declare(strict_types=1);

namespace App\Modules\Union\Entity\Union\Fields\Doctrine;

use App\Modules\Union\Entity\Union\Fields\CoverFileId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class CoverFileIdType extends StringType
{
    public const NAME = 'union_cover_file_id';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof CoverFileId) {
            return $value->getValue();
        }

        return (null !== $value) ? (string)$value : null;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?CoverFileId
    {
        return !empty($value) ? new CoverFileId((string)$value) : null;
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

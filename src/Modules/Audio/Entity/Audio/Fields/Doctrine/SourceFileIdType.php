<?php

declare(strict_types=1);

namespace App\Modules\Audio\Entity\Audio\Fields\Doctrine;

use App\Modules\Audio\Entity\Audio\Fields\SourceFileId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class SourceFileIdType extends StringType
{
    public const NAME = 'audio_source_file_id';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof SourceFileId) {
            return $value->getValue();
        }

        return (null !== $value) ? (string)$value : null;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?SourceFileId
    {
        return !empty($value) ? new SourceFileId((string)$value) : null;
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

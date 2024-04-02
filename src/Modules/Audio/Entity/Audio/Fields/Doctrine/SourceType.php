<?php

declare(strict_types=1);

namespace App\Modules\Audio\Entity\Audio\Fields\Doctrine;

use App\Modules\Audio\Entity\Audio\Fields\Source;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonType;

final class SourceType extends JsonType
{
    public const NAME = 'audio_source';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof Source) {
            return $value->getValue();
        }

        return (null !== $value) ? (string)$value : null;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Source
    {
        return !empty($value) ? new Source((string)$value) : null;
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

<?php

declare(strict_types=1);

namespace App\Modules\Audio\Entity\Audio\Fields\Doctrine;

use App\Modules\Audio\Entity\Audio\Fields\SourceHost;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class SourceHostType extends StringType
{
    public const NAME = 'audio_source_host';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof SourceHost) {
            return $value->getValue();
        }

        return (null !== $value) ? (string)$value : null;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?SourceHost
    {
        return !empty($value) ? new SourceHost((string)$value) : null;
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

<?php

declare(strict_types=1);

namespace App\Modules\Audio\Entity\AudioPlaylist\Fields\Doctrine;

use App\Modules\Audio\Entity\AudioPlaylist\Fields\PhotoHost;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class PhotoHostType extends StringType
{
    public const NAME = 'audio_playlist_photo_host';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof PhotoHost) {
            return $value->getValue();
        }

        return (null !== $value) ? (string)$value : null;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?PhotoHost
    {
        return !empty($value) ? new PhotoHost((string)$value) : null;
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

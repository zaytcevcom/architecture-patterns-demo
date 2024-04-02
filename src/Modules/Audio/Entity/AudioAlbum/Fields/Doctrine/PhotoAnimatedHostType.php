<?php

declare(strict_types=1);

namespace App\Modules\Audio\Entity\AudioAlbum\Fields\Doctrine;

use App\Modules\Audio\Entity\AudioAlbum\Fields\PhotoAnimatedHost;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class PhotoAnimatedHostType extends StringType
{
    public const NAME = 'audio_album_photo_animated_host';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof PhotoAnimatedHost) {
            return $value->getValue();
        }

        return (null !== $value) ? (string)$value : null;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?PhotoAnimatedHost
    {
        return !empty($value) ? new PhotoAnimatedHost((string)$value) : null;
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

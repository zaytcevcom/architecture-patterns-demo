<?php

declare(strict_types=1);

namespace App\Modules\Audio\Entity\AudioAlbum\Fields\Doctrine;

use App\Modules\Audio\Entity\AudioAlbum\Fields\PhotoAnimated;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonType;

final class PhotoAnimatedType extends JsonType
{
    public const NAME = 'audio_album_photo_animated';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof PhotoAnimated) {
            return $value->getValue();
        }

        return (null !== $value) ? (string)$value : null;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?PhotoAnimated
    {
        return !empty($value) ? new PhotoAnimated((string)$value) : null;
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

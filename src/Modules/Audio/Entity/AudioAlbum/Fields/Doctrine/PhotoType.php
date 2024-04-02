<?php

declare(strict_types=1);

namespace App\Modules\Audio\Entity\AudioAlbum\Fields\Doctrine;

use App\Modules\Audio\Entity\AudioAlbum\Fields\Photo;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonType;

final class PhotoType extends JsonType
{
    public const NAME = 'audio_album_photo';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof Photo) {
            return $value->getValue();
        }

        return (null !== $value) ? (string)$value : null;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Photo
    {
        return !empty($value) ? new Photo((string)$value) : null;
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

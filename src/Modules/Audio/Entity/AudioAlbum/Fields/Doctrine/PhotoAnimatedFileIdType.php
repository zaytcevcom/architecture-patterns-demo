<?php

declare(strict_types=1);

namespace App\Modules\Audio\Entity\AudioAlbum\Fields\Doctrine;

use App\Modules\Audio\Entity\AudioAlbum\Fields\PhotoAnimatedFileId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class PhotoAnimatedFileIdType extends StringType
{
    public const NAME = 'audio_album_photo_animated_file_id';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof PhotoAnimatedFileId) {
            return $value->getValue();
        }

        return (null !== $value) ? (string)$value : null;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?PhotoAnimatedFileId
    {
        return !empty($value) ? new PhotoAnimatedFileId((string)$value) : null;
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

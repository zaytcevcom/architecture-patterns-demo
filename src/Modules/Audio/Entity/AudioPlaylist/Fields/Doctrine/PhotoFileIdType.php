<?php

declare(strict_types=1);

namespace App\Modules\Audio\Entity\AudioPlaylist\Fields\Doctrine;

use App\Modules\Audio\Entity\AudioPlaylist\Fields\PhotoFileId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class PhotoFileIdType extends StringType
{
    public const NAME = 'audio_playlist_photo_file_id';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof PhotoFileId) {
            return $value->getValue();
        }

        return (null !== $value) ? (string)$value : null;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?PhotoFileId
    {
        return !empty($value) ? new PhotoFileId((string)$value) : null;
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

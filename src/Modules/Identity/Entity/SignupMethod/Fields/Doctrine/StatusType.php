<?php

declare(strict_types=1);

namespace App\Modules\Identity\Entity\SignupMethod\Fields\Doctrine;

use App\Modules\Identity\Entity\SignupMethod\Fields\Status;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\IntegerType;

final class StatusType extends IntegerType
{
    public const NAME = 'identity_signup_method_status';

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?int
    {
        return $value instanceof Status ? $value->getValue() : (int)$value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Status
    {
        return !empty($value) ? new Status((string)$value) : null;
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

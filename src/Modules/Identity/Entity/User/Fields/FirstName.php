<?php

declare(strict_types=1);

namespace App\Modules\Identity\Entity\User\Fields;

use Webmozart\Assert\Assert;
use ZayMedia\Shared\Components\Validator\Regex;

final class FirstName
{
    private string $value;

    public function __construct(string $value)
    {
        //        Assert::regex($value, Regex::firstName());

        $value = mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}

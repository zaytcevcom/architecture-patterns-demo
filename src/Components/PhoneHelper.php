<?php

declare(strict_types=1);

namespace App\Components;

class PhoneHelper
{
    public static function cleaner(string $phone): string
    {
        $phone = str_replace([' ', '(', ')', '+', '-'], '', $phone);

        if (is_numeric($phone) && \strlen($phone) === 11 && str_starts_with($phone, '8')) {
            $phone = '7' . substr($phone, 1);
        }

        return $phone;
    }
}

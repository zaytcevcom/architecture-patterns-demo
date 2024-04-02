<?php

declare(strict_types=1);

use ZayMedia\Shared\Http\Middleware\IpAddress;

return [
    IpAddress::class => static function (): IpAddress {
        return new IpAddress(true, []);
    },
];

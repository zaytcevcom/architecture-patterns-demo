<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Helpers;

enum NotifierSound: string
{
    case DEFAULT        = 'default';
    case NOTIFICATION   = 'notification.wav';
}

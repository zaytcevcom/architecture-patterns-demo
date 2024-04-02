<?php

declare(strict_types=1);

namespace App\Modules\Union\Helpers\Permissions;

enum Role: int
{
    case MEMBER  = 0;
    case CREATOR = 1;
    case ADMIN   = 2;
}

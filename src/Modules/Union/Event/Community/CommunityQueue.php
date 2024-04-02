<?php

declare(strict_types=1);

namespace App\Modules\Union\Event\Community;

enum CommunityQueue: string
{
    case CREATED = 'community-created';
    case UPDATED = 'community-updated';
}

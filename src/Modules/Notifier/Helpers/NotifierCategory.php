<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Helpers;

enum NotifierCategory: string
{
    case BADGE                      = 'BADGE';
    case PUSH_REMOVE                = 'PUSH_REMOVE';

    case CONTACT_ADDED              = 'CONTACT_ADDED';
    case CONTACT_NEW_REQUEST        = 'CONTACT_NEW_REQUEST';

    case PHOTO_COMMENT_ANSWERED     = 'PHOTO_COMMENT_ANSWERED';
    case PHOTO_COMMENTED            = 'PHOTO_COMMENTED';
    case PHOTO_COMMENT_LIKED        = 'PHOTO_COMMENT_LIKED';
    case PHOTO_LIKED                = 'PHOTO_LIKED';

    case POST_COMMENT_ANSWERED      = 'POST_COMMENT_ANSWERED';
    case POST_COMMENTED             = 'POST_COMMENTED';
    case POST_COMMENT_LIKED         = 'POST_COMMENT_LIKED';
    case POST_LIKED                 = 'POST_LIKED';
    case POST_REPOSTED              = 'POST_REPOSTED';
    case POST_PUBLISHED             = 'POST_PUBLISHED';

    case VIDEO_COMMENT_ANSWERED     = 'VIDEO_COMMENT_ANSWERED';
    case VIDEO_COMMENTED            = 'VIDEO_COMMENTED';
    case VIDEO_COMMENT_LIKED        = 'VIDEO_COMMENT_LIKED';
    case VIDEO_LIKED                = 'VIDEO_LIKED';

    case FLOW_COMMENT_ANSWERED      = 'FLOW_COMMENT_ANSWERED';
    case FLOW_COMMENTED             = 'FLOW_COMMENTED';
    case FLOW_COMMENT_LIKED         = 'FLOW_COMMENT_LIKED';
    case FLOW_LIKED                 = 'FLOW_LIKED';
    case FLOW_REPOSTED              = 'FLOW_REPOSTED';

    case CONVERSATION_NEW_MESSAGE   = 'CONVERSATION_NEW_MESSAGE';
}

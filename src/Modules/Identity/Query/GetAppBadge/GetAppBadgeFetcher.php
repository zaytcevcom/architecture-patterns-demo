<?php

declare(strict_types=1);

namespace App\Modules\Identity\Query\GetAppBadge;

use App\Modules\Contact\Query\GetRequestsInCount\ContactsGetRequestsInCountFetcher;
use App\Modules\Messenger\Query\Conversation\GetCountUnread\ConversationGetCountUnreadFetcher;
use App\Modules\Union\Query\Community\GetRequestsCount\UnionGetRequestsCountFetcher;
use Doctrine\DBAL\Exception;

final readonly class GetAppBadgeFetcher
{
    public function __construct(
        private ContactsGetRequestsInCountFetcher $requestsInCountFetcher,
        private UnionGetRequestsCountFetcher $unionGetRequestsCountFetcher,
        private ConversationGetCountUnreadFetcher $conversationGetCountUnreadFetcher,
    ) {}

    /** @throws Exception */
    public function fetch(GetAppBadgeQuery $query): int
    {
        $badge = $this->requestsInCountFetcher->fetch($query->userId);
        $badge += $this->unionGetRequestsCountFetcher->fetch($query->userId);
        $badge += $this->conversationGetCountUnreadFetcher->fetch($query->userId);

        return $badge;
    }
}

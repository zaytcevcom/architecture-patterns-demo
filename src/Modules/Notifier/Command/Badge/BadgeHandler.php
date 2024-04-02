<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Badge;

use App\Modules\Identity\Query\GetAppBadge\GetAppBadgeFetcher;
use App\Modules\Identity\Query\GetAppBadge\GetAppBadgeQuery;
use App\Modules\Notifier\Service\Pusher;
use App\Modules\OAuth\Query\GetPushTokensByUserId\GetPushTokensByUserIdFetcher;

final readonly class BadgeHandler
{
    public function __construct(
        private GetPushTokensByUserIdFetcher $pushTokensByUserIdFetcher,
        private GetAppBadgeFetcher $getAppBadgeFetcher,
        private Pusher\Pusher $pusher,
    ) {}

    public function handle(BadgeCommand $command): void
    {
        $tokens = $this->pusher->getGroupedTokens(
            $this->pushTokensByUserIdFetcher->fetch($command->userId)
        );

        if (empty($tokens)) {
            return;
        }

        $badge = $this->getAppBadgeFetcher->fetch(
            new GetAppBadgeQuery($command->userId)
        );

        foreach ($tokens as $info) {
            $this->pusher->sendBadge(
                new Pusher\BadgeCommand(
                    bundleId: $info['bundleId'],
                    platform: $info['platform'],
                    tokens: $info['tokens'],
                    badge: $badge,
                )
            );
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Flow\FlowLikedRemove;

use App\Modules\Notifier\Helpers\NotifierCategory;
use App\Modules\Notifier\Helpers\NotifierHelper;
use App\Modules\Notifier\Service\Pusher\HideCommand;
use App\Modules\Notifier\Service\Pusher\Pusher;
use App\Modules\OAuth\Query\GetPushTokensByUserId\GetPushTokensByUserIdFetcher;

final readonly class FlowLikedRemoveHandler
{
    public function __construct(
        private GetPushTokensByUserIdFetcher $pushTokensByUserIdFetcher,
        private Pusher $pusher,
        private NotifierHelper $notifierHelper,
    ) {}

    public function handle(FlowLikedRemoveCommand $command): void
    {
        $category = NotifierCategory::PUSH_REMOVE;

        $data = [
            'id' => $this->notifierHelper->getId(NotifierCategory::FLOW_LIKED, $command->likeId),
        ];

        $tokens = $this->pusher->getGroupedTokens(
            $this->pushTokensByUserIdFetcher->fetch($command->userId)
        );

        if (empty($tokens)) {
            return;
        }

        foreach ($tokens as $info) {
            $this->pusher->sendHide(
                new HideCommand(
                    bundleId: $info['bundleId'],
                    platform: $info['platform'],
                    tokens: $info['tokens'],
                    data: $data,
                    category: $category->value,
                )
            );
        }
    }
}

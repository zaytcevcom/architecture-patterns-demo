<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Call;

use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Notifier\Helpers\NotifierHelper;
use App\Modules\Notifier\Service\Pusher\Pusher;
use App\Modules\Notifier\Service\Pusher\VoIPCommand;
use App\Modules\OAuth\Query\GetPushTokensByUserId\GetPushTokensByUserIdFetcher;
use ZayMedia\Shared\Components\Realtime\Realtime;

final readonly class CallHandler
{
    public function __construct(
        private GetPushTokensByUserIdFetcher $pushTokensByUserIdFetcher,
        private UserRepository $userRepository,
        private Pusher $pusher,
        private NotifierHelper $notifierHelper,
        private Realtime $realtime,
    ) {}

    public function handle(CallCommand $command): void
    {
        if ($command->sourceId === $command->targetId) {
            return;
        }

        if (!$user = $this->userRepository->findById($command->sourceId)) {
            return;
        }

        $connectionToken = $this->realtime->generateConnectionToken((string)$command->targetId, time() + 7 * 24 * 3600);
        $channelToken    = $this->realtime->generateSubscriptionToken((string)$command->targetId, $command->channel, time() + 7 * 24 * 3600);

        $tokens = $this->pusher->getGroupedVoipTokens(
            $this->pushTokensByUserIdFetcher->fetch($command->targetId)
        );

        foreach ($tokens as $info) {
            $callerName = $this->notifierHelper->getFirstName($user, $info['locale']) . ' ' . $this->notifierHelper->getLastName($user, $info['locale']);

            $this->pusher->sendVoIP(
                new VoIPCommand(
                    bundleId: $info['bundleId'],
                    platform: $info['platform'],
                    tokens: $info['tokens'],
                    data: [
                        'callId'        => $command->callId,
                        'roomId'        => $command->roomId,
                        'uuid'          => $command->uuid,
                        'userId'        => $command->sourceId,
                        'callerName'    => $callerName,
                        'handle'        => $this->notifierHelper->getUserLink($user->getId()),
                        'photo'         => $this->notifierHelper->getUserPhoto($user),
                        'realtime'      => [
                            'connection'    => $command->connection,
                            'token'         => $connectionToken,
                            'channel'       => $command->channel,
                            'channelToken'  => $channelToken,
                        ],
                        'iceServers'    => [
                            [
                                'urls' => [$command->stunHost],
                            ],
                            [
                                'urls' => [$command->stunHost2],
                            ],
                            [
                                'urls'              => [$command->turnHost],
                                'username'          => $command->turnLogin,
                                'credential'        => $command->turnPassword,
                                'credentialType'    => 'password',
                            ],
                            [
                                'urls'              => [$command->turnHost2],
                                'username'          => $command->turnLogin2,
                                'credential'        => $command->turnPassword2,
                                'credentialType'    => 'password',
                            ],
                        ],
                    ],
                )
            );
        }
    }
}

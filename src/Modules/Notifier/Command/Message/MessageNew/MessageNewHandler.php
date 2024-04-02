<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Message\MessageNew;

use App\Modules\Identity\Entity\User\User;
use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Identity\Query\GetAppBadge\GetAppBadgeFetcher;
use App\Modules\Identity\Query\GetAppBadge\GetAppBadgeQuery;
use App\Modules\Messenger\Entity\Conversation\ConversationRepository;
use App\Modules\Messenger\Entity\ConversationSettings\ConversationSettings;
use App\Modules\Messenger\Entity\ConversationSettings\ConversationSettingsRepository;
use App\Modules\Messenger\Entity\Message\Message;
use App\Modules\Messenger\Entity\Message\MessageRepository;
use App\Modules\Messenger\Query\ConversationMember\GetNotMutedOwnerIds\ConversationMembersGetNotMutedOwnerIdsFetcher;
use App\Modules\Messenger\Query\ConversationMember\GetNotMutedOwnerIds\ConversationMembersGetNotMutedOwnerIdsQuery;
use App\Modules\Notifier\Helpers\NotifierCategory;
use App\Modules\Notifier\Helpers\NotifierHelper;
use App\Modules\Notifier\Helpers\NotifierSound;
use App\Modules\Notifier\Service\Pusher\Command;
use App\Modules\Notifier\Service\Pusher\Pusher;
use App\Modules\OAuth\Query\GetPushTokensByUserId\GetPushTokensByUserIdFetcher;
use App\Modules\Union\Entity\Union\Union;
use App\Modules\Union\Entity\Union\UnionRepository;

final readonly class MessageNewHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private UnionRepository $unionRepository,
        private MessageRepository $messageRepository,
        private ConversationRepository $conversationRepository,
        private ConversationSettingsRepository $conversationSettingsRepository,
        private GetPushTokensByUserIdFetcher $pushTokensByUserIdFetcher,
        private ConversationMembersGetNotMutedOwnerIdsFetcher $conversationMembersGetNotMutedOwnerIdsFetcher,
        private GetAppBadgeFetcher $getAppBadgeFetcher,
        private Pusher $pusher,
        private NotifierHelper $notifierHelper,
    ) {}

    public function handle(MessageNewCommand $command): void
    {
        $this->clean();

        $message = $this->messageRepository->getById($command->messageId);
        $conversation = $this->conversationRepository->getById($message->getConversationId());

        $text = $this->generateText($message);

        $user = null;

        if (!$conversation->isDialog()) {
            $user = $this->userRepository->getById($message->getUserId());
            $info = $this->conversationSettingsRepository->getByConversationId($conversation->getId());
            $title = $info->getTitle();
            $subtitle = '%firstName %lastName';
            $photo = ConversationSettings::getPhotoParsed($info->getPhoto()?->getValue())['xs'] ?? null;
        } elseif ($unionId = $message->getUnionId()) {
            $union = $this->unionRepository->getById($unionId);
            $title = $union->getName();
            $subtitle = null;
            $photo = Union::getPhotoParsed($union->getPhoto()?->getValue())['xs'] ?? null;
        } else {
            $user = $this->userRepository->getById($message->getUserId());
            $title = '%firstName %lastName';
            $subtitle = null;
            $photo = User::getPhotoParsed($user->getPhoto()?->getValue())['xs'] ?? null;
        }

        /** @var array{src: string}|string|null $photo */
        if (\is_array($photo)) {
            $photo = $photo['src'] ?? null;
        }

        $category   = NotifierCategory::CONVERSATION_NEW_MESSAGE;
        $thread     = $this->notifierHelper->getThreadName($category, $message->getConversationId());

        $data = [
            'link'          => $this->notifierHelper->getMessageLink($conversation->getId(), $message->getId()),
            'iconUrl'       => null,
            'thumbnailUrl'  => null,
            'attachmentUrl' => $photo,
            'locale'        => [
                'textInputTitle'            => 'BUTTON_1',
                'textInputButtonTitle'      => 'BUTTON_7',
                'textInputPlaceholder'      => 'PLACEHOLDER_1',
            ],
            'id'                => $this->notifierHelper->getId($category, $message->getId()),
            'conversationId'    => $conversation->getId(),
            'messageId'         => $message->getId(),
            'userId'            => $message->getOwnerId(),
        ];

        $userIds = $this->conversationMembersGetNotMutedOwnerIdsFetcher->fetch(
            new ConversationMembersGetNotMutedOwnerIdsQuery($message->getConversationId())
        );

        foreach ($userIds as $userId) {
            if ($userId === $message->getUserId()) {
                continue;
            }

            $tokens = $this->pusher->getGroupedTokens(
                $this->pushTokensByUserIdFetcher->fetch($userId)
            );

            if (empty($tokens)) {
                continue;
            }

            $badge = $this->getAppBadgeFetcher->fetch(
                new GetAppBadgeQuery($userId)
            );

            foreach ($tokens as $info) {
                $this->pusher->send(
                    new Command(
                        bundleId: $info['bundleId'],
                        platform: $info['platform'],
                        locale: $info['locale'],
                        tokens: $info['tokens'],
                        title: $title,
                        body: $text,
                        subtitle: $subtitle,
                        category: $category->value,
                        thread: $thread,
                        data: $data,
                        badge: $badge,
                        sound: NotifierSound::DEFAULT->value,
                        translateParams: [
                            '%firstName' => (null !== $user) ? $this->notifierHelper->getFirstName($user, $info['locale']) : null,
                            '%lastName'  => (null !== $user) ? $this->notifierHelper->getLastName($user, $info['locale']) : null,
                        ]
                    )
                );
            }
        }
    }

    private function generateText(Message $message): string
    {
        return $message->getText() ?? '';
    }

    private function clean(): void
    {
        $this->userRepository->clear();
        $this->unionRepository->clear();
        $this->conversationSettingsRepository->clear();
    }
}

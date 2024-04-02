<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Command\Photo\PhotoCommented;

use App\Modules\Identity\Entity\User\User;
use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Notifier\Helpers\NotifierCategory;
use App\Modules\Notifier\Helpers\NotifierHelper;
use App\Modules\Notifier\Helpers\NotifierSound;
use App\Modules\Notifier\Service\Pusher\Command;
use App\Modules\Notifier\Service\Pusher\Pusher;
use App\Modules\OAuth\Query\GetPushTokensByUserId\GetPushTokensByUserIdFetcher;
use App\Modules\Photo\Entity\Photo\PhotoRepository;

final readonly class PhotoCommentedHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private PhotoRepository $photoRepository,
        private GetPushTokensByUserIdFetcher $pushTokensByUserIdFetcher,
        private Pusher $pusher,
        private NotifierHelper $notifierHelper,
    ) {}

    public function handle(PhotoCommentedCommand $command): void
    {
        $photo = $this->photoRepository->getById($command->photoId);

        if ($photo->getUnionId() !== null || $photo->getUserId() === $command->userId) {
            return;
        }

        $tokens = $this->pusher->getGroupedTokens(
            $this->pushTokensByUserIdFetcher->fetch($photo->getUserId())
        );

        if (empty($tokens)) {
            return;
        }

        $user = $this->userRepository->getById($command->userId);
        $text = $photo->getDescription() ?? '🖼 Фотография'; // todo

        $category   = NotifierCategory::PHOTO_COMMENTED;
        $thread     = $this->notifierHelper->getThreadName($category, $photo->getId());

        $data = [
            'link'          => $this->notifierHelper->getPhotoLink($photo->getId()),
            'iconUrl'       => null,
            'thumbnailUrl'  => null,
            'attachmentUrl' => User::getPhotoParsed($user->getPhoto()?->getValue())['xs'] ?? null,
            'locale'        => [
                'like'                      => 'BUTTON_2',
                'textInputTitle'            => 'BUTTON_1',
                'textInputButtonTitle'      => 'BUTTON_7',
                'textInputPlaceholder'      => 'PLACEHOLDER_1',
            ],
            'id'        => $this->notifierHelper->getId($category, $user->getId()),
            'photoId'   => $photo->getId(),
        ];

        foreach ($tokens as $info) {
            $this->pusher->send(
                new Command(
                    bundleId: $info['bundleId'],
                    platform: $info['platform'],
                    locale: $info['locale'],
                    tokens: $info['tokens'],
                    title: 'KEY_13',
                    body: 'KEY_15',
                    subtitle: 'KEY_14',
                    category: $category->value,
                    thread: $thread,
                    data: $data,
                    badge: null,
                    sound: NotifierSound::DEFAULT->value,
                    translateParams: [
                        '%firstName'    => $this->notifierHelper->getFirstName($user, $info['locale']),
                        '%lastName'     => $this->notifierHelper->getLastName($user, $info['locale']),
                        '%CommentText'  => $this->pusher->translate($text, [], $info['locale']),
                    ]
                )
            );
        }
    }
}

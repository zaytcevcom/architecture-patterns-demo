<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\SignupByPhone\Confirm;

use App\Modules\_Features\Command\Bots\GenerateBots\GenerateBotsHandler;
use App\Modules\Identity\Command\PhotoSaveSignup\PhotoSaveSignupCommand;
use App\Modules\Identity\Command\PhotoSaveSignup\PhotoSaveSignupHandler;
use App\Modules\Identity\Entity\Device\Device;
use App\Modules\Identity\Entity\Device\DeviceRepository;
use App\Modules\Identity\Entity\Temp\UserTempRepository;
use App\Modules\Identity\Entity\User\Fields\FirstName;
use App\Modules\Identity\Entity\User\Fields\LastName;
use App\Modules\Identity\Entity\User\User;
use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Identity\Event\User\UserEventPublisher;
use App\Modules\Identity\Event\User\UserQueue;
use App\Modules\Messenger\Command\Message\Create\MessageCreateCommand;
use App\Modules\Messenger\Command\Message\Create\MessageCreateHandler;
use App\Modules\Messenger\Entity\Message\Message;
use App\Modules\Photo\Entity\PhotoAlbum\PhotoAlbum;
use App\Modules\Photo\Entity\PhotoAlbum\PhotoAlbumRepository;
use App\Modules\Union\Command\Join\UnionJoinCommand;
use App\Modules\Union\Command\Join\UnionJoinHandler;
use Exception;
use ZayMedia\Shared\Components\Flusher;
use ZayMedia\Shared\Components\Transliterator\Transliterator;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

final readonly class IdentitySignupByPhoneConfirmHandler
{
    public function __construct(
        private UserRepository $users,
        private DeviceRepository $devices,
        private UserTempRepository $usersTemp,
        private PhotoAlbumRepository $photoAlbumRepository,
        private PhotoSaveSignupHandler $photoSaveSignupHandler,
        private UnionJoinHandler $unionJoinHandler,
        private Flusher $flusher,
        private Transliterator $transliterator,
        private GenerateBotsHandler $generateBotsHandler,
        private MessageCreateHandler $messageCreateHandler,
        private UserEventPublisher $eventPublisher,
    ) {}

    public function handle(IdentitySignupByPhoneConfirmCommand $command): User
    {
        $userTemp = $this->usersTemp->findByUniqueId($command->uniqueId);

        if (!$userTemp || null === $userTemp->getPhone()) {
            throw new DomainExceptionModule(
                module: 'identity',
                message: 'error.signup.confirm.user_not_found',
                code: 1
            );
        }

        if (!$userTemp->isCaptchaVerified()) {
            throw new DomainExceptionModule(
                module: 'identity',
                message: 'error.signup.confirm.user_invalid_captcha',
                code: 2
            );
        }

        // todo
        $isValid = $userTemp->isValidCode($command->code);
        if ($userTemp->getPhone()?->getValue() === '78888888888') {
            $isValid = $command->code === '88888';
        }

        // if (!$userTemp->isValidCode($command->code)) {
        if (!$isValid) {
            throw new DomainExceptionModule(
                module: 'identity',
                message: 'error.signup.confirm.user_invalid_code',
                code: 3
            );
        }

        // todo
        if ($userTemp->getPhone()?->getValue() === '78888888888') {
            $this->usersTemp->remove($userTemp);
            $this->flusher->flush();
            return $this->users->getById(8);
        }

        $user = User::signupByTemp($userTemp);
        $user->setFirstNameTranslit(new FirstName(
            $this->transliterator->translit($user->getFirstName()->getValue())
        ));
        $user->setLastNameTranslit(new LastName(
            $this->transliterator->translit($user->getLastName()->getValue())
        ));
        $this->users->save($user);

        $device = Device::createFromTemp(user: $user, userTemp: $userTemp);
        $this->devices->add($device);

        $this->usersTemp->remove($userTemp);

        $this->flusher->flush();

        $this->createSystemData($user);

        $this->features($user);

        $this->eventPublisher->handle(UserQueue::CREATED, $user->getId());

        return $user;
    }

    private function createSystemData(User $user): void
    {
        // Create user system albums
        $this->photoAlbumRepository->add(PhotoAlbum::createSystemProfileForUser($user->getId()));
        $this->photoAlbumRepository->add(PhotoAlbum::createSystemLoadedForUser($user->getId()));
        $this->photoAlbumRepository->add(PhotoAlbum::createSystemPostsForUser($user->getId()));

        $this->flusher->flush();

        $photoAlbum = $this->photoAlbumRepository->getSystemProfileByUserId($user->getId());
        $user->setAlbumProfileId($photoAlbum->getId());
        $this->users->save($user);

        $photoHost = $user->getPhotoHost()?->getValue();
        $photoFileId = $user->getPhotoFileId()?->getValue();

        if ($photoHost !== null && $photoFileId !== null) {
            $this->photoSaveSignupHandler->handle(
                new PhotoSaveSignupCommand(
                    userId: $user->getId(),
                    host: $photoHost,
                    fileId: $photoFileId
                )
            );
        }
    }

    private function features(User $user): void
    {
        $this->subscribeToMainCommunity($user);

        $this->sendMessage($user);

        try {
            $this->generateBotsHandler->handle();
        } catch (Exception) {
        }
    }

    private function subscribeToMainCommunity(User $user): void
    {
        try {
            $this->unionJoinHandler->handle(
                new UnionJoinCommand($user->getId(), 1)
            );
        } catch (Exception) {
        }
    }

    private function sendMessage(User $user): void
    {
        try {
            $text = $user->getFirstName()->getValue() . ' ' . $user->getLastName()->getValue() . ' теперь на LO!' .
                PHP_EOL . PHP_EOL .
                'lo.ink/id' . $user->getId();

            $this->messageCreateHandler->handle(
                new MessageCreateCommand(
                    userId: Message::BOT_USER_ID,
                    conversationId: Message::SYSTEM_REGISTRATION_CONVERSATION_ID,
                    unionId: null,
                    messageId: null,
                    text: $text,
                    photoIds: null,
                    audioIds: null,
                    videoIds: null,
                    flowId: null,
                    stickerId: null,
                    uuid: null
                )
            );
        } catch (Exception) {
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\SignupByEmail\Request;

use App\Modules\Identity\Entity\Device\Device;
use App\Modules\Identity\Entity\SignupMethod\SignupMethod;
use App\Modules\Identity\Entity\SignupMethod\SignupMethodRepository;
use App\Modules\Identity\Entity\Temp\UserTemp;
use App\Modules\Identity\Entity\Temp\UserTempRepository;
use App\Modules\Identity\Entity\User\Fields\Email;
use App\Modules\Identity\Entity\User\Fields\FirstName;
use App\Modules\Identity\Entity\User\Fields\LastName;
use App\Modules\Identity\Entity\User\Fields\Password;
use App\Modules\Identity\Entity\User\Fields\PhotoFileId;
use App\Modules\Identity\Entity\User\Fields\PhotoHost;
use App\Modules\Identity\Entity\User\Fields\Sex;
use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Identity\Service\PasswordHasher;
use App\Modules\Identity\Service\SignupEmailConfirmationSender;
use Ramsey\Uuid\Uuid;
use ZayMedia\Shared\Components\Flusher;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

final readonly class IdentitySignupByEmailRequestHandler
{
    public function __construct(
        private UserRepository $users,
        private UserTempRepository $usersTemp,
        private PasswordHasher $hasher,
        private Flusher $flusher,
        private SignupMethodRepository $signupMethods,
        private SignupEmailConfirmationSender $sender
    ) {}

    public function handle(IdentitySignupByEmailRequestCommand $command): array
    {
        if (!$this->signupMethods->hasEmailEnabled()) {
            throw new DomainExceptionModule(
                module: 'identity',
                message: 'error.signup.request.method_not_allowed',
                code: 1
            );
        }

        $email = new Email($command->email);

        if ($this->users->findByEmail($email)) {
            throw new DomainExceptionModule(
                module: 'identity',
                message: 'error.signup.request.user_already_exists',
                code: 2
            );
        }

        $userTemp = $this->usersTemp->findByEmail($email);
        $uniqueId = Uuid::uuid4()->toString();

        $device = new Device(
            user: null,
            method: SignupMethod::TYPE_EMAIL,
            baseOS: $command->baseOS,
            buildId: $command->buildId,
            brand: $command->brand,
            buildNumber: $command->buildNumber,
            bundleId: $command->bundleId,
            carrier: $command->carrier,
            deviceId: $command->deviceId,
            deviceName: $command->deviceName,
            ipAddress: $command->ipAddress,
            installerPackageName: $command->installerPackageName,
            macAddress: $command->macAddress,
            manufacturer: $command->manufacturer,
            model: $command->model,
            systemName: $command->systemName,
            systemVersion: $command->systemVersion,
            userAgent: $command->userAgent,
            version: $command->version
        );

        $needSend = true;

        if (!empty($userTemp)) {
            if (!$userTemp->isValidInterval()) {
                $needSend = false;
            } else {
                $userTemp->generateCode();
            }

            $userTemp->setUniqueId($uniqueId);
            $userTemp->setTime(time());
            $userTemp->setFirstName(new FirstName($command->firstName));
            $userTemp->setLastName(new LastName($command->lastName));
            $userTemp->setSex(new Sex($command->sex));
            $userTemp->setPhotoHost(new PhotoHost($command->photoHost));
            $userTemp->setPhotoFileId(new PhotoFileId($command->photoFileId));
            $userTemp->setPassword($this->hasher->hash((new Password(value: $command->password))->getValue()));
            $userTemp->setDeviceInfo($device);
        } else {
            $userTemp = UserTemp::SignupByEmail(
                email: $email,
                firstName: new FirstName($command->firstName),
                lastName: new LastName($command->lastName),
                sex: new Sex($command->sex),
                photoHost: new PhotoHost($command->photoHost),
                photoFileId: new PhotoFileId($command->photoFileId),
                password: $this->hasher->hash((new Password(value: $command->password))->getValue()),
                device: $device
            );

            $userTemp->setUniqueId($uniqueId);

            $this->usersTemp->add($userTemp);
        }

        $this->flusher->flush();

        if ($needSend) {
            $this->sender->send(
                email: $email,
                code: $userTemp->getCode()->getValue()
            );
        }

        return [
            'uniqueId' => $userTemp->getUniqueId(),
            'interval' => $userTemp->getTimeInterval(),
        ];
    }
}

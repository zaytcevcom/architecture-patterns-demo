<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\SignupByPhone\Request;

use App\Components\LoCaptcha;
use App\Modules\Identity\Entity\Device\Device;
use App\Modules\Identity\Entity\SignupMethod\SignupMethod;
use App\Modules\Identity\Entity\SignupMethod\SignupMethodRepository;
use App\Modules\Identity\Entity\Temp\UserTemp;
use App\Modules\Identity\Entity\Temp\UserTempRepository;
use App\Modules\Identity\Entity\User\Fields\FirstName;
use App\Modules\Identity\Entity\User\Fields\LastName;
use App\Modules\Identity\Entity\User\Fields\Password;
use App\Modules\Identity\Entity\User\Fields\Phone;
use App\Modules\Identity\Entity\User\Fields\PhotoFileId;
use App\Modules\Identity\Entity\User\Fields\PhotoHost;
use App\Modules\Identity\Entity\User\Fields\Sex;
use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Identity\Service\PasswordHasher;
use Ramsey\Uuid\Uuid;
use ZayMedia\Shared\Components\Flusher;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

final readonly class IdentitySignupByPhoneRequestHandler
{
    public function __construct(
        private UserRepository $users,
        private UserTempRepository $usersTemp,
        private PasswordHasher $hasher,
        private Flusher $flusher,
        private SignupMethodRepository $signupMethods,
        private LoCaptcha $captcha
    ) {}

    public function handle(IdentitySignupByPhoneRequestCommand $command): array
    {
        if (!$this->signupMethods->hasSmsEnabled()) {
            throw new DomainExceptionModule(
                module: 'identity',
                message: 'error.signup.request.method_not_allowed',
                code: 1
            );
        }

        $phone = new Phone($command->phone);

        if ($this->users->findByPhone($phone)) {
            throw new DomainExceptionModule(
                module: 'identity',
                message: 'error.signup.request.user_already_exists',
                code: 2
            );
        }

        $userTemp = $this->usersTemp->findByPhone($phone);
        $uniqueId = Uuid::uuid4()->toString();

        $device = new Device(
            user: null,
            method: SignupMethod::TYPE_SMS,
            baseOS: $command->baseOS,
            buildId: $command->buildId,
            brand: $command->brand,
            buildNumber: $command->buildNumber,
            bundleId: $command->bundleId,
            carrier: $command->carrier,
            deviceId: $command->deviceId,
            deviceName: $command->deviceName,
            ipAddress: $command->ipAddress,
            ipReal: $command->ipReal,
            installerPackageName: $command->installerPackageName,
            macAddress: $command->macAddress,
            manufacturer: $command->manufacturer,
            model: $command->model,
            systemName: $command->systemName,
            systemVersion: $command->systemVersion,
            userAgent: $command->userAgent,
            version: $command->version
        );

        if (!empty($userTemp)) {
            $userTemp->generateCode();
            $userTemp->resetCaptchaVerified();
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
            $userTemp = UserTemp::SignupByPhone(
                phone: $phone,
                firstName: new FirstName($command->firstName),
                lastName: new LastName($command->lastName),
                sex: new Sex((int)$command->sex),
                photoHost: new PhotoHost($command->photoHost),
                photoFileId: new PhotoFileId($command->photoFileId),
                password: $this->hasher->hash((new Password(value: $command->password))->getValue()),
                device: $device
            );

            $userTemp->setUniqueId($uniqueId);

            $this->usersTemp->add($userTemp);
        }

        $this->flusher->flush();

        return [
            'uniqueId' => $userTemp->getUniqueId(),
            'captcha'  => $this->captcha->get($uniqueId),
        ];
    }
}

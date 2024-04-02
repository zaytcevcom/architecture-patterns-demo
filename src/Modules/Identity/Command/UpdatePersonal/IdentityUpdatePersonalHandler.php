<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\UpdatePersonal;

use App\Modules\Data\Entity\City\CityRepository;
use App\Modules\Data\Entity\Country\CountryRepository;
use App\Modules\Identity\Entity\User\Fields\Marital;
use App\Modules\Identity\Entity\User\Fields\Phone;
use App\Modules\Identity\Entity\User\Fields\Site;
use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Identity\Event\User\UserEventPublisher;
use App\Modules\Identity\Event\User\UserQueue;
use ZayMedia\Shared\Components\Flusher;

final readonly class IdentityUpdatePersonalHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private CountryRepository $countryRepository,
        private CityRepository $cityRepository,
        private Flusher $flusher,
        private UserEventPublisher $eventPublisher,
    ) {}

    public function handle(IdentityUpdatePersonalCommand $command): void
    {
        $user = $this->userRepository->getById($command->userId);

        $marital = (null !== $command->marital) ? new Marital($command->marital) : null;
        $user->setMarital($marital);
        $user->setMaritalStatus($marital?->getValue() ? (string)$marital->getValue() : null);

        if (null !== $command->maritalId) {
            $userMarital = $this->userRepository->getById($command->maritalId);
            $user->setMaritalId($userMarital->getId());
        } else {
            $user->setMaritalId(null);
        }

        $country = (null !== $command->contactCountryId) ? $this->countryRepository->findById($command->contactCountryId) : null;
        $user->setContactCountry($country);

        $city = (null !== $command->contactCityId) ? $this->cityRepository->findById($command->contactCityId) : null;
        $user->setContactCity($city);

        $site = (!empty($command->contactSite)) ? new Site($command->contactSite) : null;
        $user->setContactSite($site);

        $phone = (!empty($command->contactPhone)) ? new Phone($command->contactPhone) : null;
        $user->setContactPhone($phone);

        $this->flusher->flush();

        $this->eventPublisher->handle(UserQueue::UPDATED, $user->getId());
    }
}

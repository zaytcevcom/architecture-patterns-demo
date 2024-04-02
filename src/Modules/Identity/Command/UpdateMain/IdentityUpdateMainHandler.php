<?php

declare(strict_types=1);

namespace App\Modules\Identity\Command\UpdateMain;

use App\Modules\Data\Entity\City\CityRepository;
use App\Modules\Data\Entity\Country\CountryRepository;
use App\Modules\Identity\Entity\User\Fields\Birthday;
use App\Modules\Identity\Entity\User\Fields\FirstName;
use App\Modules\Identity\Entity\User\Fields\LastName;
use App\Modules\Identity\Entity\User\Fields\Sex;
use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Identity\Event\User\UserEventPublisher;
use App\Modules\Identity\Event\User\UserQueue;
use ZayMedia\Shared\Components\Flusher;
use ZayMedia\Shared\Components\Transliterator\Transliterator;

final readonly class IdentityUpdateMainHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private CountryRepository $countryRepository,
        private CityRepository $cityRepository,
        private Flusher $flusher,
        private Transliterator $transliterator,
        private UserEventPublisher $eventPublisher,
    ) {}

    public function handle(IdentityUpdateMainCommand $command): void
    {
        $user = $this->userRepository->getById($command->userId);

        if (null !== $command->firstName) {
            $user->setFirstName(new FirstName($command->firstName));
            $user->setFirstNameTranslit(new FirstName(
                $this->transliterator->translit($user->getFirstName()->getValue())
            ));
        }

        if (null !== $command->lastName) {
            $user->setLastName(new LastName($command->lastName));
            $user->setLastNameTranslit(new LastName(
                $this->transliterator->translit($user->getLastName()->getValue())
            ));
        }

        if (null !== $command->sex) {
            $user->setSex(new Sex($command->sex));
        }

        if (null !== $command->birthday) {
            $user->setBirthday(new Birthday($command->birthday));
        }

        if (null !== $command->countryId) {
            $country = $this->countryRepository->getById($command->countryId);
            $user->setCountry($country);
        }

        if (null !== $command->cityId) {
            $city = $this->cityRepository->getById($command->cityId);
            $user->setCity($city);
        }

        $this->flusher->flush();

        $this->eventPublisher->handle(UserQueue::UPDATED, $user->getId());
    }
}

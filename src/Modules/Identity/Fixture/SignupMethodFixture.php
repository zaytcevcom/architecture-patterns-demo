<?php

declare(strict_types=1);

namespace App\Modules\Identity\Fixture;

use App\Modules\Identity\Entity\SignupMethod\Fields\Status;
use App\Modules\Identity\Entity\SignupMethod\SignupMethod;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

final class SignupMethodFixture extends AbstractFixture
{
    public function load(ObjectManager $manager): void
    {
        $signupMethodSms = new SignupMethod(
            name: 'sms',
            status: Status::active()
        );

        $signupMethodEmail = new SignupMethod(
            name: 'email',
            status: Status::active()
        );

        $manager->persist($signupMethodSms);

        $manager->persist($signupMethodEmail);

        $manager->flush();
    }
}

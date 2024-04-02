<?php

declare(strict_types=1);

namespace App\Modules\Identity\Fixture;

use App\Modules\Identity\Entity\User\Fields\Email;
use App\Modules\Identity\Entity\User\Fields\FirstName;
use App\Modules\Identity\Entity\User\Fields\LastName;
use App\Modules\Identity\Entity\User\Fields\Sex;
use App\Modules\Identity\Entity\User\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

final class UserFixture extends AbstractFixture
{
    public function load(ObjectManager $manager): void
    {
        //        $user = User::SignupByEmail(
        //            email: new Email('zaytcev@zay.media'),
        //            firstName: new FirstName('Konstantin'),
        //            lastName: new LastName('Zaytcev'),
        //            sex: Sex::male(),
        //            password: '1234567890'
        //        );
        //
        //        $manager->persist($user);
        //
        //        $manager->flush();
    }
}

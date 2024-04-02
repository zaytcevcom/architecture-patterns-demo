<?php

declare(strict_types=1);

namespace App\Modules\Identity\Entity\User;

use App\Modules\Identity\Entity\User\Fields\Email;
use App\Modules\Identity\Entity\User\Fields\FirstName;
use App\Modules\Identity\Entity\User\Fields\LastName;
use App\Modules\Identity\Entity\User\Fields\Phone;
use App\Modules\Identity\Entity\User\Fields\ScreenName;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

class UserRepository
{
    /**
     * @var EntityRepository<User>
     */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(User::class);
        $this->em = $em;
    }

    public function getById(int $id): User
    {
        if (!$user = $this->findById($id)) {
            throw new DomainExceptionModule(
                module: 'identity',
                message: 'error.user.user_not_found',
                code: 1
            );
        }

        return $user;
    }

    public function findById(int $id): ?User
    {
        return $this->repo->findOneBy(['id' => $id]);
    }

    public function findByPhone(Phone $phone): ?User
    {
        return $this->repo->findOneBy(['phone' => $phone]);
    }

    public function findByEmail(Email $email): ?User
    {
        return $this->repo->findOneBy(['email' => $email]);
    }

    public function findByScreenName(ScreenName $screenName): ?User
    {
        return $this->repo->findOneBy(['screenName' => $screenName]);
    }

    public function findByEmailForRestore(Email $email, FirstName $firstName, LastName $lastName): ?User
    {
        return $this->repo->findOneBy([
            'email'        => $email,
            'firstName'    => $firstName,
            'lastName'     => $lastName,
        ]);
    }

    public function findByPhoneForRestore(Phone $phone, FirstName $firstName, LastName $lastName): ?User
    {
        return $this->repo->findOneBy([
            'phone'        => $phone,
            'firstName'    => $firstName,
            'lastName'     => $lastName,
        ]);
    }

    public function add(User $user): void
    {
        $this->em->persist($user);
    }

    public function save(User $user): void
    {
        $this->em->persist($user);
        $this->em->flush();
    }

    public function remove(User $user): void
    {
        $this->em->remove($user);
    }

    public function clear(): void
    {
        $this->em->clear();
    }
}

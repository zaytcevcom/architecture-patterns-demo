<?php

declare(strict_types=1);

namespace App\Modules\Identity\Entity\Temp;

use App\Modules\Identity\Entity\User\Fields\Email;
use App\Modules\Identity\Entity\User\Fields\Phone;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class UserTempRepository
{
    /**
     * @var EntityRepository<UserTemp>
     */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(UserTemp::class);
        $this->em = $em;
    }

    public function findByUniqueId(string $uniqueId): ?UserTemp
    {
        return $this->repo->findOneBy(['uniqueId' => $uniqueId]);
    }

    public function findByPhone(Phone $phone): ?UserTemp
    {
        return $this->repo->findOneBy(['phone' => $phone]);
    }

    public function findByEmail(Email $email): ?UserTemp
    {
        return $this->repo->findOneBy(['email' => $email]);
    }

    public function add(UserTemp $user): void
    {
        $this->em->persist($user);
    }

    public function remove(UserTemp $user): void
    {
        $this->em->remove($user);
    }
}

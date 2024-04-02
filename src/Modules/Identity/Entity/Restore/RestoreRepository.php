<?php

declare(strict_types=1);

namespace App\Modules\Identity\Entity\Restore;

use App\Modules\Identity\Entity\User\Fields\Email;
use App\Modules\Identity\Entity\User\Fields\Phone;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class RestoreRepository
{
    /**
     * @var EntityRepository<Restore>
     */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(Restore::class);
        $this->em = $em;
    }

    public function findByUniqueId(string $uniqueId): ?Restore
    {
        return $this->repo->findOneBy(['uniqueId' => $uniqueId, 'isDone' => 0]);
    }

    public function countAttemptsByEmailToday(Email $email): int
    {
        $timeFrom = strtotime(date('Y-m-d'));

        return $this->repo
            ->matching(
                Criteria::create()
                    ->where(Criteria::expr()->eq('email', $email->getValue()))
                    ->andWhere(Criteria::expr()->gt('time', $timeFrom))
                    ->andWhere(Criteria::expr()->isNull('user'))
            )
            ->count();
    }

    public function countAttemptsByPhoneToday(Phone $phone): int
    {
        $timeFrom = strtotime(date('Y-m-d'));

        return $this->repo
            ->matching(
                Criteria::create()
                    ->where(Criteria::expr()->eq('phone', $phone->getValue()))
                    ->andWhere(Criteria::expr()->gt('time', $timeFrom))
                    ->andWhere(Criteria::expr()->isNull('user'))
            )
            ->count();
    }

    public function add(Restore $restore): void
    {
        $this->em->persist($restore);
    }

    public function remove(Restore $restore): void
    {
        $this->em->remove($restore);
    }
}

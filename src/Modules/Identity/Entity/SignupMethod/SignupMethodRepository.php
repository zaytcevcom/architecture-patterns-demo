<?php

declare(strict_types=1);

namespace App\Modules\Identity\Entity\SignupMethod;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class SignupMethodRepository
{
    /**
     * @var EntityRepository<SignupMethod>
     */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(SignupMethod::class);
        $this->em = $em;
    }

    public function hasSmsEnabled(): bool
    {
        return $this->repo->count(['name' => 'sms', 'status' => 1]) === 1;
    }

    public function hasEmailEnabled(): bool
    {
        return $this->repo->count(['name' => 'email', 'status' => 1]) === 1;
    }

    public function add(SignupMethod $signupMethod): void
    {
        $this->em->persist($signupMethod);
    }

    public function remove(SignupMethod $signupMethod): void
    {
        $this->em->remove($signupMethod);
    }
}

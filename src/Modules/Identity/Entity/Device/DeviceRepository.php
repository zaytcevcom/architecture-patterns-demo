<?php

declare(strict_types=1);

namespace App\Modules\Identity\Entity\Device;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

class DeviceRepository
{
    /**
     * @var EntityRepository<Device>
     */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(Device::class);
        $this->em = $em;
    }

    public function getByUserId(int $userId): Device
    {
        if (!$device = $this->findByUserId($userId)) {
            throw new DomainExceptionModule(
                module: 'identity',
                message: 'error.device.not_found',
                code: 1
            );
        }

        return $device;
    }

    public function findByUserId(int $userId): ?Device
    {
        return $this->repo->findOneBy(['userId' => $userId]);
    }

    public function add(Device $device): void
    {
        $this->em->persist($device);
    }

    public function remove(Device $device): void
    {
        $this->em->remove($device);
    }
}

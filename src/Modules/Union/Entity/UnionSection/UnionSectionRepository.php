<?php

declare(strict_types=1);

namespace App\Modules\Union\Entity\UnionSection;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

class UnionSectionRepository
{
    /**
     * @var EntityRepository<UnionSection>
     */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(UnionSection::class);
        $this->em = $em;
    }

    public function getByUnionId(int $unionId): UnionSection
    {
        if (!$unionSection = $this->findByUnionId($unionId)) {
            throw new DomainExceptionModule(
                module: 'union',
                message: 'error.union.union_section_not_found',
                code: 1
            );
        }

        return $unionSection;
    }

    public function findByUnionId(int $unionId): ?UnionSection
    {
        return $this->repo->findOneBy([
            'unionId' => $unionId,
        ]);
    }

    public function add(UnionSection $unionSection): void
    {
        $this->em->persist($unionSection);
    }

    public function remove(UnionSection $unionSection): void
    {
        $this->em->remove($unionSection);
    }
}

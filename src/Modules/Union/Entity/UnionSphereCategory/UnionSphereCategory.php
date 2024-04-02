<?php

declare(strict_types=1);

namespace App\Modules\Union\Entity\UnionSphereCategory;

use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity]
#[ORM\Table(name: 'unions_spheres_categories')]
class UnionSphereCategory
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', unique: true)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(name: 'sphere_id', type: 'integer')]
    private int $categoryId;

    #[ORM\Column(name: 'category_id', type: 'integer')]
    private int $subcategoryId;

    private function __construct(
        int $categoryId,
        int $subcategoryId,
    ) {
        $this->categoryId = $categoryId;
        $this->subcategoryId = $subcategoryId;
    }

    public function getId(): int
    {
        if (null === $this->id) {
            throw new DomainException('Id not set');
        }
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function setCategoryId(int $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function getSubcategoryId(): int
    {
        return $this->subcategoryId;
    }

    public function setSubcategoryId(int $subcategoryId): void
    {
        $this->subcategoryId = $subcategoryId;
    }
}

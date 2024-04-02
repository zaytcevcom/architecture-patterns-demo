<?php

declare(strict_types=1);

namespace App\Modules\Union\Entity\UnionSphere;

use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity]
#[ORM\Table(name: 'unions_spheres')]
#[ORM\Index(fields: ['name'], name: 'IDX_SEARCH')]
class UnionSphere
{
    private const TYPE_COMMUNITY    = 0;
    private const TYPE_PLACE        = 1;
    private const TYPE_EVENT        = 2;

    #[ORM\Id]
    #[ORM\Column(type: 'integer', unique: true)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(name: 'union_type', type: 'integer')]
    private int $type;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $verified = 0;

    #[ORM\Column(name: 'union_count', type: 'integer', options: ['default' => 0])]
    private int $count = 0;

    private function __construct(
        int $type,
        string $name,
    ) {
        $this->type = $type;
        $this->name = $name;
    }

    public static function typeCommunity(): int
    {
        return self::TYPE_COMMUNITY;
    }

    public static function typePlace(): int
    {
        return self::TYPE_PLACE;
    }

    public static function typeEvent(): int
    {
        return self::TYPE_EVENT;
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

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): void
    {
        $this->type = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getVerified(): int
    {
        return $this->verified;
    }

    public function setVerified(int $verified): void
    {
        $this->verified = $verified;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function setCount(int $count): void
    {
        $this->count = $count;
    }

    public function toArray(): array
    {
        return [
            'id'       => $this->getId(),
            'type'     => $this->getType(),
            'name'     => $this->getName(),
            'verified' => $this->getVerified(),
            'count'    => $this->getCount(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Audio\Entity\AudioGenre;

use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity]
#[ORM\Table(name: 'audios_genre')]
#[ORM\Index(fields: ['name'], name: 'IDX_SEARCH')]
class AudioGenre
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', unique: true)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'string')]
    private string $name;

    private function __construct(
        string $name
    ) {
        $this->name = $name;
    }

    public static function create(
        string $name
    ): self {
        return new self(
            name: $name,
        );
    }

    public function edit(
        string $name
    ): void {
        $this->name = $name;
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function toArray(): array
    {
        return [
            'id'        => $this->getId(),
            'name'      => $this->getName(),
        ];
    }
}

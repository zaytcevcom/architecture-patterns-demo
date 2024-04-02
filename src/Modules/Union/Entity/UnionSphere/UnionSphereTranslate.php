<?php

declare(strict_types=1);

namespace App\Modules\Union\Entity\UnionSphere;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'unions_spheres_translate')]
#[ORM\Index(fields: ['lang'], name: 'IDX_CODE')]
#[ORM\Index(fields: ['value'], name: 'IDX_SEARCH')]
class UnionSphereTranslate
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', unique: true)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $sphereId;

    #[ORM\Column(type: 'string')]
    private string $lang;

    #[ORM\Column(type: 'string')]
    private string $value;

    public function __construct(
        int $sphereId,
        string $lang,
        string $value,
    ) {
        $this->sphereId = $sphereId;
        $this->lang = $lang;
        $this->value = $value;
    }

    public function getSphereId(): int
    {
        return $this->sphereId;
    }

    public function setSphereId(int $sphereId): void
    {
        $this->sphereId = $sphereId;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function setLang(string $lang): void
    {
        $this->lang = $lang;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }
}

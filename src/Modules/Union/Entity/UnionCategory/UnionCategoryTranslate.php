<?php

declare(strict_types=1);

namespace App\Modules\Union\Entity\UnionCategory;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'unions_categories_translate')]
#[ORM\Index(fields: ['lang'], name: 'IDX_CODE')]
#[ORM\Index(fields: ['value'], name: 'IDX_SEARCH')]
class UnionCategoryTranslate
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', unique: true)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $categoryId;

    #[ORM\Column(type: 'string')]
    private string $lang;

    #[ORM\Column(type: 'string')]
    private string $value;

    public function __construct(
        int $categoryId,
        string $lang,
        string $value,
    ) {
        $this->categoryId = $categoryId;
        $this->lang = $lang;
        $this->value = $value;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function setCategoryId(int $categoryId): void
    {
        $this->categoryId = $categoryId;
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

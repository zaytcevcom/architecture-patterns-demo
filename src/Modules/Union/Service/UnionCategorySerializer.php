<?php

declare(strict_types=1);

namespace App\Modules\Union\Service;

class UnionCategorySerializer
{
    public function serialize(?array $unionCategory): ?array
    {
        if (empty($unionCategory)) {
            return null;
        }

        return [
            'id'   => $unionCategory['id'],
            'name' => $unionCategory['name'] ?? '',
        ];
    }

    public function serializeItems(array $items): array
    {
        $result = [];

        /** @var array $item */
        foreach ($items as $item) {
            $result[] = $this->serialize($item);
        }

        return $result;
    }
}

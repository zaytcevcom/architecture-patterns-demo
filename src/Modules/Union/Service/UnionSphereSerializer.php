<?php

declare(strict_types=1);

namespace App\Modules\Union\Service;

class UnionSphereSerializer
{
    public function serialize(?array $unionSphere): ?array
    {
        if (empty($unionSphere)) {
            return null;
        }

        return [
            'id'   => $unionSphere['id'],
            'name' => $unionSphere['name'] ?? '',
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

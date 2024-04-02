<?php

declare(strict_types=1);

namespace App\Modules\Union\Service;

class UnionContactSerializer
{
    public function serialize(?array $unionContact): ?array
    {
        if (empty($unionContact)) {
            return null;
        }

        return [
            'id'        => $unionContact['id'],
            'userId'    => $unionContact['owner_id'],
            'position'  => $unionContact['position'],
            'phone'     => $unionContact['phone'],
            'email'     => $unionContact['email'],
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

<?php

declare(strict_types=1);

namespace App\Modules\Union\Service;

class UnionLinkSerializer
{
    public function serialize(?array $unionLink): ?array
    {
        if (empty($unionLink)) {
            return null;
        }

        return [
            'id'     => $unionLink['id'],
            'photo'  => null,
            'url'    => $unionLink['url'],
            'title'  => $unionLink['title'],
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

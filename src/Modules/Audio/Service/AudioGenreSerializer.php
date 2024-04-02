<?php

declare(strict_types=1);

namespace App\Modules\Audio\Service;

class AudioGenreSerializer
{
    public function serialize(?array $audioGenre): ?array
    {
        if (empty($audioGenre)) {
            return null;
        }

        return [
            'id'   => $audioGenre['id'],
            'name' => $audioGenre['name'] ?? '',
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

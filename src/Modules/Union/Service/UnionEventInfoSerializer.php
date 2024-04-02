<?php

declare(strict_types=1);

namespace App\Modules\Union\Service;

class UnionEventInfoSerializer
{
    public function serialize(?array $union): ?array
    {
        if (empty($union)) {
            return null;
        }

        return [
            'id'        => $union['id'],
            'unionId'   => $union['union_id'],
            'timeStart' => $union['time_start'],
            'timeEnd'   => $union['time_end'],
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

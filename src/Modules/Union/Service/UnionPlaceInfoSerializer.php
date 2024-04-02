<?php

declare(strict_types=1);

namespace App\Modules\Union\Service;

use function App\Components\env;

class UnionPlaceInfoSerializer
{
    public function serialize(?array $union): ?array
    {
        if (empty($union)) {
            return null;
        }

        /** @var string|null $geo */
        $geo = $union['geolocation'] ?? null;
        $latitude = null;
        $longitude = null;
        $map = null;

        if ($geo) {
            $geo = explode(',', trim($geo, '[]'));

            if (\count($geo) === 2) {
                $latitude = (float)$geo[0];
                $longitude = (float)$geo[1];

                $map = env('MAP_HOST') . '#13.9/' . $latitude . '/' . $longitude;
            }
        }

        /** @var string|null $workingHours */
        $workingHours = $union['working_hours'] ?? null;

        return [
            'unionId'           => $union['union_id'],
            'address'           => $union['location'],
            'latitude'          => $latitude,
            'longitude'         => $longitude,
            'map'               => $map,
            'open'              => false,
            'email'             => $union['email'],
            'phone'             => $union['phone'],
            'phoneDescription'  => $union['phone_description'],
            'workingHours'      => $this->getWorkingHours($workingHours),
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

    private function getWorkingHours(?string $json): array
    {
        $workingHours = [];

        if (null === $json) {
            return $this->getDefault();
        }

        /** @var array{
         *     from: int|null,
         *     before: int|null,
         *     isWorking: bool|null
         * }[]|array{
         *     from: int|null,
         *     to: int|null,
         *     closed: bool|null
         * }[] $items
         */
        $items = json_decode($json, true);

        if (empty($items)) {
            return $this->getDefault();
        }

        foreach ($items as $item) {
            $workingHours[] = [
                'from'      => $item['from'] ?? 0,
                'to'        => $item['before'] ?? $item['to'] ?? 0,
                'closed'    => (isset($item['isWorking'])) ? !$item['isWorking'] : $item['closed'] ?? false,
            ];
        }

        return $workingHours;
    }

    private function getDefault(): array
    {
        $workingHours = [];

        for ($i = 0; $i < 7; ++$i) {
            $workingHours[] = [
                'from'      => 0,
                'to'        => 0,
                'closed'    => false,
            ];
        }

        return $workingHours;
    }
}

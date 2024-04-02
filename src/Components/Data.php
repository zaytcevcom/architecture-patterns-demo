<?php

declare(strict_types=1);

namespace App\Components;

interface Data
{
    public function suggestAddress(?string $query): array;

    public function suggestAddressByGeo(float $latitude, float $longitude): array;

    public function getGeoByAddress(?string $address): ?object;

    public function getCityNameByAddress(?string $address): ?string;
}

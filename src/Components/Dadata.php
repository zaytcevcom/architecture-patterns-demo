<?php

declare(strict_types=1);

namespace App\Components;

use Dadata\DadataClient;
use Exception;

final readonly class Dadata implements Data
{
    public function __construct(
        private DadataClient $dadata,
    ) {}

    /** Предлагаемые адреса на основе пользовательского ввода. */
    public function suggestAddress(?string $query): array
    {
        if (empty($query)) {
            return [];
        }

        try {
            $dadata = (array)$this->dadata->suggest('address', $query);
        } catch (Exception) {
            return [];
        }

        if (\count($dadata) > 0) {
            return $dadata;
        }

        return [];
    }

    /** Предлагаемые адреса на основе геопозиции. */
    public function suggestAddressByGeo(float $latitude, float $longitude): array
    {
        try {
            $dadata = (array)$this->dadata->geolocate('address', $latitude, $longitude);
        } catch (Exception) {
            return [];
        }

        if (\count($dadata) > 0) {
            return $dadata;
        }

        return [];
    }

    /** Получение геопозиции по адресу. */
    public function getGeoByAddress(?string $address): ?object
    {
        if (empty($address)) {
            return null;
        }

        try {
            $dadata = (array)$this->dadata->clean('address', $address);
        } catch (Exception) {
            return null;
        }

        if (
            isset($dadata['geo_lat'], $dadata['geo_lon']) &&
            is_numeric($dadata['geo_lat']) && is_numeric($dadata['geo_lon'])
        ) {
            return (object)[
                'lat' => (string)$dadata['geo_lat'],
                'lon' => (string)$dadata['geo_lon'],
            ];
        }

        return null;
    }

    /** Получение названия города по адресу. */
    public function getCityNameByAddress(?string $address): ?string
    {
        if (empty($address)) {
            return null;
        }

        try {
            $dadata = (array)$this->dadata->clean('address', $address);
        } catch (Exception) {
            return null;
        }

        if (isset($dadata['city'])) {
            return (string)$dadata['city'];
        }

        if (isset($dadata['region'])) {
            return (string)$dadata['region'];
        }

        return null;
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Audio\Query\Audio\GetLyrics;

use App\Modules\Audio\Entity\Audio\AudioRepository;
use GuzzleHttp\Client;
use Throwable;

final readonly class GetLyricsFetcher
{
    public function __construct(
        private AudioRepository $audioRepository,
        private Client $client,
    ) {}

    public function fetch(GetLyricsQuery $query): ?string
    {
        $audio = $this->audioRepository->getById($query->id);

        if ($lyrics = $audio->getLyrics()) {
            try {
                $response = $this->client->get($lyrics);

                if ($response->getStatusCode() === 200) {
                    return (string)$response->getBody();
                }
            } catch (Throwable $e) {
                return $e->getMessage();
            }
        }

        return null;
    }
}

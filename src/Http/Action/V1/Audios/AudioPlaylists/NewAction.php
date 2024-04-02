<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Audios\AudioPlaylists;

use App\Http\Action\Unifier\Audio\AudioPlaylistUnifier;
use App\Modules\Audio\Query\AudioPlaylist\New\AudioPlaylistNewFetcher;
use App\Modules\Audio\Query\AudioPlaylist\New\AudioPlaylistNewQuery;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Helpers\OpenApi\ResponseSuccessful;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataItemsResponse;

#[OA\Get(
    path: '/audio-playlists/new',
    description: 'Новые плейлисты',
    summary: 'Новые плейлисты',
    security: [['bearerAuth' => '{}']],
    tags: ['Audios playlists'],
    responses: [new ResponseSuccessful()]
)]
#[OA\Parameter(
    name: 'count',
    description: 'Кол-во которое необходимо получить',
    in: 'query',
    required: false,
    schema: new OA\Schema(
        type: 'integer',
        format: 'int64'
    ),
    example: 100
)]
#[OA\Parameter(
    name: 'offset',
    description: 'Смещение',
    in: 'query',
    required: false,
    schema: new OA\Schema(
        type: 'integer',
        format: 'int64'
    ),
    example: 0
)]
final readonly class NewAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private AudioPlaylistNewFetcher $fetcher,
        private Validator $validator,
        private AudioPlaylistUnifier $unifier
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $query = $this->denormalizer->denormalizeQuery($request->getQueryParams(), AudioPlaylistNewQuery::class);

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query);

        return new JsonDataItemsResponse(
            count: $result->count,
            items: $this->unifier->unify($identity->id, $result->items)
        );
    }
}

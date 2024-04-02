<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Audios\AudioAlbums;

use App\Http\Action\Unifier\Audio\AudioAlbumUnifier;
use App\Modules\Audio\Query\AudioAlbum\New\AudioAlbumNewFetcher;
use App\Modules\Audio\Query\AudioAlbum\New\AudioAlbumNewQuery;
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
    path: '/audio-albums/new',
    description: 'Новые синглы и альбомы',
    summary: 'Новые синглы и альбомы',
    security: [['bearerAuth' => '{}']],
    tags: ['Audios albums'],
    responses: [new ResponseSuccessful()]
)]
#[OA\Parameter(
    name: 'filter',
    description: 'albums - только альбомы, singles - только синглы, иначе - все',
    in: 'query',
    required: false,
    schema: new OA\Schema(
        type: 'string'
    ),
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
        private AudioAlbumNewFetcher $fetcher,
        private Validator $validator,
        private AudioAlbumUnifier $unifier
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $query = $this->denormalizer->denormalizeQuery($request->getQueryParams(), AudioAlbumNewQuery::class);

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query);

        return new JsonDataItemsResponse(
            count: $result->count,
            items: $this->unifier->unify($identity->id, $result->items)
        );
    }
}

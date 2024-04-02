<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Data;

use App\Http\Action\Unifier\Data\SpaceUnifier;
use App\Modules\Data\Query\Space\GetByLocation\DataGetByLocationFetcher;
use App\Modules\Data\Query\Space\GetByLocation\DataGetByLocationQuery;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

#[OA\Get(
    path: '/data/spaces/location',
    description: 'Получение пространства по местоположению',
    summary: 'Получение пространства по местоположению',
    security: [['bearerAuth' => '{}']],
    tags: ['Data']
)]
#[OA\Parameter(
    name: 'latitude',
    description: 'latitude',
    in: 'query',
    required: true,
    schema: new OA\Schema(
        type: 'string'
    ),
    example: 52.5072747
)]
#[OA\Parameter(
    name: 'longitude',
    description: 'longitude',
    in: 'query',
    required: true,
    schema: new OA\Schema(
        type: 'string'
    ),
    example: 85.1472004
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class GetSpaceByLocationAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private DataGetByLocationFetcher $fetcher,
        private Validator $validator,
        private SpaceUnifier $spaceUnifier,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        Authenticate::getIdentity($request);

        $query = $this->denormalizer->denormalizeQuery(
            data: $request->getQueryParams(),
            type: DataGetByLocationQuery::class
        );

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query);

        return new JsonDataResponse([
            'space' => (null !== $result) ? $this->spaceUnifier->unifyOne(null, $result) : null,
        ]);
    }
}

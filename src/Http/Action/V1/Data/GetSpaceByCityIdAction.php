<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Data;

use App\Http\Action\Unifier\Data\SpaceUnifier;
use App\Modules\Data\Query\Space\GetByCityId\DataGetByCityIdFetcher;
use App\Modules\Data\Query\Space\GetByCityId\DataGetByCityIdQuery;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

#[OA\Get(
    path: '/data/spaces/city/{cityId}',
    description: 'Получение пространства по городу',
    summary: 'Получение пространства по городу',
    security: [['bearerAuth' => '{}']],
    tags: ['Data']
)]
#[OA\Parameter(
    name: 'cityId',
    description: 'Идентификатор города',
    in: 'path',
    required: true,
    schema: new OA\Schema(
        type: 'integer',
        format: 'int64'
    ),
    example: 1
)]
#[OA\Response(
    response: 200,
    description: 'Successful operation'
)]
final readonly class GetSpaceByCityIdAction implements RequestHandlerInterface
{
    public function __construct(
        private DataGetByCityIdFetcher $fetcher,
        private Validator $validator,
        private SpaceUnifier $spaceUnifier,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $query = new DataGetByCityIdQuery(
            Route::getArgumentToInt($request, 'cityId')
        );

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query);

        return new JsonDataResponse([
            'space' => (null !== $result) ? $this->spaceUnifier->unifyOne(null, $result) : null,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Unions\Places;

use App\Modules\Union\Query\UnionSphere\Place\UnionSpherePlaceFetcher;
use App\Modules\Union\Query\UnionSphere\Place\UnionSpherePlaceQuery;
use App\Modules\Union\Service\UnionSphereSerializer;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\Translation\Translator;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataItemsResponse;

#[OA\Get(
    path: '/places/spheres',
    description: 'Список сфер мест',
    summary: 'Список сфер мест',
    security: [['bearerAuth' => '{}']],
    tags: ['Places']
)]
#[OA\Parameter(
    name: 'search',
    description: 'Поисковый запрос',
    in: 'query',
    required: false,
    schema: new OA\Schema(
        type: 'string'
    ),
)]
#[OA\Parameter(
    name: 'sort',
    description: 'Сортировка (0 - по убыванию, 1 - по возрастания)',
    in: 'query',
    required: false,
    schema: new OA\Schema(
        type: 'integer',
        format: 'int64'
    ),
    example: 0
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
#[OA\Response(
    response: 200,
    description: 'Successful operation'
)]
final readonly class GetSpheresAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private UnionSpherePlaceFetcher $fetcher,
        private Validator $validator,
        private UnionSphereSerializer $serializer,
        private Translator $translator
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        Authenticate::getIdentity($request);

        $query = $this->denormalizer->denormalizeQuery(
            array_merge(
                $request->getQueryParams(),
                ['locale' => $this->translator->getLocale()]
            ),
            UnionSpherePlaceQuery::class
        );

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query);

        return new JsonDataItemsResponse(
            count: $result->count,
            items: $this->serializer->serializeItems($result->items)
        );
    }
}

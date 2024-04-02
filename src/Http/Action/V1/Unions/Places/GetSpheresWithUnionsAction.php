<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Unions\Places;

use App\Http\Action\Unifier\Union\UnionUnifier;
use App\Modules\Union\Query\Place\Search\PlaceSearchFetcher;
use App\Modules\Union\Query\Place\Search\PlaceSearchQuery;
use App\Modules\Union\Query\UnionSphere\Place\UnionSpherePlaceFetcher;
use App\Modules\Union\Query\UnionSphere\Place\UnionSpherePlaceQuery;
use App\Modules\Union\Query\UnionSphere\PlaceWithSpace\UnionSpherePlaceWithSpaceQuery;
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
    path: '/places/spheres-unions',
    description: 'Список сфер с местами',
    summary: 'Список сфер с местами',
    security: [['bearerAuth' => '{}']],
    tags: ['Places']
)]
#[OA\Parameter(
    name: 'spaceId',
    description: 'Идентификатор пространства',
    in: 'query',
    required: true,
    schema: new OA\Schema(
        type: 'integer',
        format: 'int64'
    ),
    example: 12
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
final readonly class GetSpheresWithUnionsAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private UnionSpherePlaceFetcher $fetcher,
        private PlaceSearchFetcher $searchFetcher,
        private Validator $validator,
        private UnionSphereSerializer $serializer,
        private UnionUnifier $unionUnifier,
        private Translator $translator
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $query = $this->denormalizer->denormalizeQuery(
            array_merge(
                $request->getQueryParams(),
                ['locale' => $this->translator->getLocale()]
            ),
            UnionSpherePlaceWithSpaceQuery::class
        );

        $this->validator->validate($query);

        $result = $this->fetcher->fetch(
            new UnionSpherePlaceQuery(
                search: $query->search,
                sort: $query->sort,
                count: $query->count,
                offset: $query->offset,
                locale: $query->locale
            )
        );

        $arr = [];

        /** @var array{id: int, name: string} $item */
        foreach ($result->items as $item) {
            /** @var array{id: int, name: string} $item */
            $item = $this->serializer->serialize($item);

            $items = $this->searchFetcher->fetch(
                new PlaceSearchQuery(
                    spaceId: $query->spaceId,
                    sphereId: $item['id'],
                    count: 10,
                    offset: 0
                )
            )->items;

            if (empty($items)) {
                continue;
            }

            $item['unions'] = $this->unionUnifier->unify(
                $identity->id,
                $items
            );

            $arr[] = $item;
        }

        return new JsonDataItemsResponse(
            count: \count($arr),
            items: $arr
        );
    }
}

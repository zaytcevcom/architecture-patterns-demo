<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Unions\Communities;

use App\Http\Action\Unifier\Union\UnionUnifier;
use App\Modules\Union\Query\Community\Search\CommunitySearchFetcher;
use App\Modules\Union\Query\Community\Search\CommunitySearchQuery;
use App\Modules\Union\Query\UnionCategory\Community\BySphere\UnionCategoryCommunityBySphereFetcher;
use App\Modules\Union\Query\UnionCategory\Community\BySphere\UnionCategoryCommunityBySphereQuery;
use App\Modules\Union\Service\UnionCategorySerializer;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\Translation\Translator;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataItemsResponse;

#[OA\Get(
    path: '/communities/spheres/{id}/categories-unions',
    description: 'Список категорий в сфере с сообществами',
    summary: 'Список категорий в сфере с сообществами',
    security: [['bearerAuth' => '{}']],
    tags: ['Communities']
)]
#[OA\Parameter(
    name: 'id',
    description: 'Идентификатор сферы',
    in: 'path',
    required: true,
    schema: new OA\Schema(
        type: 'integer',
        format: 'int64'
    ),
    example: 1
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
final readonly class GetCategoriesBySphereWithUnionsAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private UnionCategoryCommunityBySphereFetcher $fetcher,
        private CommunitySearchFetcher $searchFetcher,
        private Validator $validator,
        private UnionCategorySerializer $serializer,
        private UnionUnifier $unionUnifier,
        private Translator $translator
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $query = $this->denormalizer->denormalizeQuery(
            array_merge(
                $request->getQueryParams(),
                [
                    'sphereId' => Route::getArgumentToInt($request, 'id'),
                    'locale' => $this->translator->getLocale(),
                    'search' => null,
                ]
            ),
            UnionCategoryCommunityBySphereQuery::class
        );

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query);

        $arr = [];

        /** @var array{id: int, name: string} $item */
        foreach ($result->items as $item) {
            /** @var array{id: int, name: string} $item */
            $item = $this->serializer->serialize($item);

            if ($item['id'] === 72) {
                continue;
            }

            $items = $this->searchFetcher->fetch(
                new CommunitySearchQuery(
                    categoryId: $item['id'],
                    search: (string)($request->getQueryParams()['search'] ?? ''),
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
            count: $result->count,
            items: $arr
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Unions\Communities;

use App\Http\Action\Unifier\Union\UnionUnifier;
use App\Modules\Union\Query\Community\Search\CommunitySearchFetcher;
use App\Modules\Union\Query\Community\Search\CommunitySearchQuery;
use App\Modules\Union\Query\UnionSphere\Community\UnionSphereCommunityFetcher;
use App\Modules\Union\Query\UnionSphere\Community\UnionSphereCommunityQuery;
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
    path: '/communities/spheres-unions',
    description: 'Список сфер с сообществами',
    summary: 'Список сфер с сообществами',
    security: [['bearerAuth' => '{}']],
    tags: ['Communities']
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
        private UnionSphereCommunityFetcher $fetcher,
        private CommunitySearchFetcher $searchFetcher,
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
            UnionSphereCommunityQuery::class
        );

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query);

        $arr = [];

        /** @var array{id: int, name: string} $item */
        foreach ($result->items as $item) {
            /** @var array{id: int, name: string} $item */
            $item = $this->serializer->serialize($item);

            $item['unions'] = $this->unionUnifier->unify(
                $identity->id,
                $this->searchFetcher->fetch(
                    new CommunitySearchQuery(
                        sphereId: $item['id'],
                        count: 10,
                        offset: 0
                    )
                )->items
            );

            $arr[] = $item;
        }

        return new JsonDataItemsResponse(
            count: $result->count,
            items: $arr
        );
    }
}

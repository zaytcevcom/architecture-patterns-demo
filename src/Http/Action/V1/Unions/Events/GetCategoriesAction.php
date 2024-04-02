<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Unions\Events;

use App\Modules\Union\Query\UnionSphere\Event\UnionSphereEventFetcher;
use App\Modules\Union\Query\UnionSphere\Event\UnionSphereEventQuery;
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
    path: '/events/categories',
    description: 'Список категорий событий',
    summary: 'Список категорий событий',
    security: [['bearerAuth' => '{}']],
    tags: ['Events']
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
final readonly class GetCategoriesAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private UnionSphereEventFetcher $fetcher,
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
            UnionSphereEventQuery::class
        );

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query);

        return new JsonDataItemsResponse(
            count: $result->count,
            items: $this->serializer->serializeItems($result->items)
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Posts\Feed;

use App\Http\Action\Unifier\Post\PostUnifier;
use App\Modules\Post\Query\Feed\Get\FeedGetFetcher;
use App\Modules\Post\Query\Feed\Get\FeedGetQuery;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Helpers\OpenApi\ParameterCount;
use ZayMedia\Shared\Helpers\OpenApi\ParameterCursor;
use ZayMedia\Shared\Helpers\OpenApi\ResponseSuccessful;
use ZayMedia\Shared\Helpers\OpenApi\Security;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataItemsResponse;

#[OA\Get(
    path: '/posts/feed',
    description: 'Получение списка новостей',
    summary: 'Получение списка новостей',
    security: [Security::BEARER_AUTH],
    tags: ['Posts'],
    parameters: [new ParameterCursor(), new ParameterCount()],
    responses: [new ResponseSuccessful()]
)]
#[OA\Parameter(
    name: 'space',
    description: 'null - Все, contacts - Контакты, communities - Сообщества, places - Места, events - События',
    in: 'query',
    required: false,
    schema: new OA\Schema(
        type: 'string'
    ),
    example: null
)]
#[OA\Parameter(
    name: 'content',
    description: 'null - Все новости, photos - Фотографии, videos - Видео',
    in: 'query',
    required: false,
    schema: new OA\Schema(
        type: 'string'
    ),
    example: null
)]
final readonly class GetFeedAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private FeedGetFetcher $fetcher,
        private Validator $validator,
        private PostUnifier $unifier
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $query = $this->denormalizer->denormalizeQuery(
            array_merge(
                $request->getQueryParams(),
                ['userId' => $identity->id]
            ),
            FeedGetQuery::class
        );

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query);

        $items = $this->unifier->unify($identity->id, $result->items);

        return new JsonDataItemsResponse(
            count: $result->count,
            items: $items,
        );
    }
}

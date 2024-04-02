<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Contacts;

use App\Http\Action\Unifier\User\UserUnifier;
use App\Modules\Contact\Query\GetOnlineByUserId\ContactGetOnlineByUserIdFetcher;
use App\Modules\Contact\Query\GetOnlineByUserId\ContactGetOnlineByUserIdQuery;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataItemsResponse;

#[OA\Get(
    path: '/users/{id}/contacts/online',
    description: 'Возвращает список контактов пользователя, которые сейчас online',
    summary: 'Возвращает список контактов пользователя, которые сейчас online',
    security: [['bearerAuth' => '{}']],
    tags: ['Users (Contacts)']
)]
#[OA\Parameter(
    name: 'id',
    description: 'Идентификатор пользователя',
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
#[OA\Parameter(
    name: 'fields',
    description: 'Доп. поля (relationship, country, city, contacts, interests, position, counters, marital, career, blacklisted, blacklistedByMe)',
    in: 'query',
    required: false,
    schema: new OA\Schema(
        type: 'string'
    ),
    example: 'contacts,counters'
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class GetOnlineByUserIdAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private ContactGetOnlineByUserIdFetcher $fetcher,
        private Validator $validator,
        private UserUnifier $unifier
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::findIdentity($request);

        $query = $this->denormalizer->denormalizeQuery(
            array_merge(
                $request->getQueryParams(),
                ['userId' => Route::getArgumentToInt($request, 'id')]
            ),
            ContactGetOnlineByUserIdQuery::class
        );

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query);

        return new JsonDataItemsResponse(
            count: $result->count,
            items: $this->unifier->unify($identity?->id, $result->items, $query->fields)
        );
    }
}

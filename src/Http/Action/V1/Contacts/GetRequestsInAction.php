<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Contacts;

use App\Http\Action\Unifier\User\UserUnifier;
use App\Modules\Contact\Query\GetRequestsIn\ContactsGetRequestsInFetcher;
use App\Modules\Contact\Query\GetRequestsIn\ContactsGetRequestsInQuery;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataItemsResponse;

#[OA\Get(
    path: '/users/contacts/requests/in',
    description: 'Возвращает список входящих заявок в контакты. В поле ```mutual``` каждого пользователя возвращается список общих контактов',
    summary: 'Возвращает список входящих заявок в контакты',
    security: [['bearerAuth' => '{}']],
    tags: ['Users (Contacts)']
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
    example: '100'
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
    example: '0'
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class GetRequestsInAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private ContactsGetRequestsInFetcher $fetcher,
        private Validator $validator,
        private UserUnifier $unifier
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $query = $this->denormalizer->denormalizeQuery(
            array_merge(
                $request->getQueryParams(),
                ['userId' => $identity->id]
            ),
            ContactsGetRequestsInQuery::class
        );

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query);

        return new JsonDataItemsResponse(
            count: $result->count,
            items: $this->unifier->unifyWithMutual($identity->id, $result->items, $result->mutual, ['country', 'city'])
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Contacts;

use App\Http\Action\Unifier\User\UserUnifier;
use App\Modules\Contact\Query\GetSearchHistory\ContactsGetSearchHistoryFetcher;
use App\Modules\Contact\Query\GetSearchHistory\ContactsGetSearchHistoryQuery;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataItemsResponse;

#[OA\Get(
    path: '/users/search/history',
    description: 'Получение истории поиска по контактам',
    summary: 'Получение истории поиска по контактам',
    security: [['bearerAuth' => '{}']],
    tags: ['Users (Contacts)']
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class GetSearchHistoryAction implements RequestHandlerInterface
{
    public function __construct(
        private ContactsGetSearchHistoryFetcher $fetcher,
        private UserUnifier $unifier
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $query = new ContactsGetSearchHistoryQuery(
            userId: $identity->id
        );

        $result = $this->fetcher->fetch($query);

        return new JsonDataItemsResponse(
            count: $result->count,
            items: $this->unifier->unify($identity->id, $result->items)
        );
    }
}

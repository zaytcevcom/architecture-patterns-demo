<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Contacts;

use App\Http\Action\Unifier\User\UserUnifier;
use App\Modules\Contact\Query\GetImportant\ContactGetImportantFetcher;
use App\Modules\Contact\Query\GetImportant\ContactGetImportantQuery;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataItemsResponse;

#[OA\Get(
    path: '/users/contacts/important',
    description: 'Возвращает список важных контактов',
    summary: 'Возвращает список важных контактов',
    security: [['bearerAuth' => '{}']],
    tags: ['Users (Contacts)']
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
final readonly class GetImportantAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private ContactGetImportantFetcher $fetcher,
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
            ContactGetImportantQuery::class
        );

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query);

        return new JsonDataItemsResponse(
            count: $result->count,
            items: $this->unifier->unify($identity->id, $result->items, $query->fields)
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Users;

use App\Http\Action\Unifier\User\UserUnifier;
use App\Modules\Identity\Query\GetByIds\IdentityGetByIdsFetcherCached;
use App\Modules\Identity\Query\GetByIds\IdentityGetByIdsQuery;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataItemsResponse;

#[OA\Get(
    path: '/users',
    description: 'Получение информации о пользователях по их идентификаторам',
    summary: 'Получение информации о пользователях по их идентификаторам',
    security: [['bearerAuth' => '{}']],
    tags: ['Users']
)]
#[OA\Parameter(
    name: 'ids',
    description: 'Идентификаторы пользователей (через запятую)',
    in: 'query',
    required: true,
    schema: new OA\Schema(
        type: 'string'
    ),
    example: '1,168'
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
final readonly class GetByIdsAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private IdentityGetByIdsFetcherCached $fetcher,
        private Validator $validator,
        private UserUnifier $unifier
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::findIdentity($request);

        $query = $this->denormalizer->denormalizeQuery(
            $request->getQueryParams(),
            IdentityGetByIdsQuery::class
        );

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query);

        return new JsonDataItemsResponse(
            count: \count($result),
            items: $this->unifier->unify($identity?->id, $result, $query->fields)
        );
    }
}

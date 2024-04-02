<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Contacts;

use App\Http\Action\Unifier\User\UserUnifier;
use App\Modules\Contact\Query\GetByUserId\ContactGetByUserIdFetcher;
use App\Modules\Contact\Query\GetByUserId\ContactGetByUserIdQuery;
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

use function ZayMedia\Shared\Components\Functions\fieldToInt;

#[OA\Get(
    path: '/users/{id}/contacts',
    description: 'Возвращает список контактов пользователя',
    summary: 'Возвращает список контактов пользователя',
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
    example: '1'
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
    name: 'isGrouped',
    description: 'Сгруппировать по первой букве фамилии',
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
final readonly class GetByUserIdAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private ContactGetByUserIdFetcher $fetcher,
        private Validator $validator,
        private UserUnifier $unifier,
        private Translator $translator
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::findIdentity($request);

        /** @var string[] $data */
        $data = $request->getQueryParams();

        $isGrouped = fieldToInt($data, 'isGrouped');

        $query = $this->denormalizer->denormalizeQuery(
            array_merge(
                $request->getQueryParams(),
                ['userId' => Route::getArgumentToInt($request, 'id')]
            ),
            ContactGetByUserIdQuery::class
        );

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query, $this->translator->getLocale());

        if ($isGrouped) {
            $items = $this->unifier->unifyGroupedByLastName($identity?->id, $result->items, $query->fields);
        } else {
            $items = $this->unifier->unify($identity?->id, $result->items, $query->fields);
        }

        return new JsonDataItemsResponse(
            count: $result->count,
            items: $items
        );
    }
}

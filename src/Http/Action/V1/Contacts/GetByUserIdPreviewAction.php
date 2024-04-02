<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Contacts;

use App\Http\Action\Unifier\User\UserUnifier;
use App\Modules\Contact\Query\GetByUserIdPreview\ContactGetByUserIdPreviewFetcher;
use App\Modules\Contact\Query\GetByUserIdPreview\ContactGetByUserIdPreviewQuery;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataItemsResponse;

use function ZayMedia\Shared\Components\Functions\fieldToInt;

#[OA\Get(
    path: '/users/{id}/contacts/preview',
    description: 'Возвращает список контактов пользователя (для страницы профиля)',
    summary: 'Возвращает список контактов пользователя (для страницы профиля)',
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
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class GetByUserIdPreviewAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private ContactGetByUserIdPreviewFetcher $fetcher,
        private Validator $validator,
        private UserUnifier $unifier
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
            ContactGetByUserIdPreviewQuery::class
        );

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query);

        if ($isGrouped) {
            $items = $this->unifier->unifyGroupedByLastName($identity?->id, $result->items);
        } else {
            $items = $this->unifier->unify($identity?->id, $result->items);
        }

        return new JsonDataItemsResponse(
            count: $result->count,
            items: $items
        );
    }
}

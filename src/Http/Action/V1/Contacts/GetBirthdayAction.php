<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Contacts;

use App\Http\Action\Unifier\User\UserUnifier;
use App\Modules\Contact\Query\GetBirthday\ContactGetBirthdayFetcher;
use App\Modules\Contact\Query\GetBirthday\ContactGetBirthdayQuery;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataItemsResponse;

use function ZayMedia\Shared\Components\Functions\fieldToInt;

#[OA\Get(
    path: '/users/contacts/birthdays',
    description: 'Получение дней рождений контактов',
    summary: 'Получение дней рождений контактов',
    security: [['bearerAuth' => '{}']],
    tags: ['Users (Contacts)']
)]
#[OA\Parameter(
    name: 'time',
    description: 'Время в timestamp с которого начинается выборка',
    in: 'query',
    required: false,
    schema: new OA\Schema(
        type: 'string'
    ),
)]
#[OA\Parameter(
    name: 'isGrouped',
    description: 'Сгруппировать по дням',
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
final readonly class GetBirthdayAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private ContactGetBirthdayFetcher $fetcher,
        private Validator $validator,
        private UserUnifier $unifier
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        /** @var string[] $data */
        $data = $request->getQueryParams();

        $isGrouped = fieldToInt($data, 'isGrouped');

        $query = $this->denormalizer->denormalizeQuery(
            array_merge(
                $request->getQueryParams(),
                ['userId' => $identity->id]
            ),
            ContactGetBirthdayQuery::class
        );

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query);

        if ($isGrouped) {
            $items = $this->unifier->unifyGroupedByDay($identity->id, $result->items);
        } else {
            $items = $this->unifier->unify($identity->id, $result->items);
        }

        return new JsonDataItemsResponse(
            count: $result->count,
            items: $items
        );
    }
}

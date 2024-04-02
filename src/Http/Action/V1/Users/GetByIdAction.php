<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Users;

use App\Http\Action\Unifier\User\UserUnifier;
use App\Modules\Contact\Command\View\ContactViewCommand;
use App\Modules\Contact\Command\View\ContactViewDefaultHandler;
use App\Modules\Identity\Query\GetById\IdentityGetByIdFetcher;
use App\Modules\Identity\Query\GetById\IdentityGetByIdQuery;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

#[OA\Get(
    path: '/users/{id}',
    description: 'Получение информации о пользователе по его идентификатору',
    summary: 'Получение информации о пользователе по его идентификатору',
    security: [['bearerAuth' => '{}']],
    tags: ['Users']
)]
#[OA\Parameter(
    name: 'id',
    description: 'Идентификатор пользователя',
    in: 'path',
    required: true,
    schema: new OA\Schema(
        type: 'integer'
    ),
    example: 1
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
final readonly class GetByIdAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private IdentityGetByIdFetcher $fetcher,
        private ContactViewDefaultHandler $handler,
        private Validator $validator,
        private UserUnifier $unifier
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::findIdentity($request);

        $query = $this->denormalizer->denormalizeQuery(
            array_merge(
                $request->getQueryParams(),
                ['id' => Route::getArgumentToInt($request, 'id')]
            ),
            IdentityGetByIdQuery::class
        );

        $this->validator->validate($query);

        /**
         * @var array{
         *     id:int,
         * } $result
         */
        $result = $this->fetcher->fetch($query);

        if ($identity) {
            $command = new ContactViewCommand(
                userId: $identity->id,
                contactId: $result['id']
            );

            $this->handler->handle($command);
        }

        return new JsonDataResponse(
            $this->unifier->unifyOne($identity?->id, $result, $query->fields)
        );
    }
}

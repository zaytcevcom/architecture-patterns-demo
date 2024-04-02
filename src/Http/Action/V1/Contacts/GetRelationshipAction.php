<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Contacts;

use App\Modules\Contact\Query\GetRelationship\ContactGetRelationshipFetcher;
use App\Modules\Contact\Query\GetRelationship\ContactGetRelationshipQuery;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

#[OA\Get(
    path: '/users/{id}/relationship/{userId}',
    description: 'Возвращает статус отношений между парой пользователей<br><br>
        **Статус ответа**:<br>
        0 - Пользователи не находятся друг у друга в контактах и нет заявок/запросов<br>
        1 - Имеется входящая заявка/подписка от пользователя {userId}<br>
        2 - Отправлен запрос пользователю {userId}<br>
        3 - {userId} отклонил заявку в контакты<br>
        4 - пользователи находятся друг у друга в контактах',
    summary: 'Возвращает статус отношений между парой пользователей',
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
)]
#[OA\Parameter(
    name: 'userId',
    description: 'Идентификатор пользователя',
    in: 'path',
    required: true,
    schema: new OA\Schema(
        type: 'integer',
        format: 'int64'
    ),
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class GetRelationshipAction implements RequestHandlerInterface
{
    public function __construct(
        private ContactGetRelationshipFetcher $fetcher,
        private Validator $validator
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        Authenticate::getIdentity($request);

        $query = new ContactGetRelationshipQuery(
            sourceId: Route::getArgumentToInt($request, 'id'),
            targetId: Route::getArgumentToInt($request, 'userId')
        );

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query);

        return new JsonDataResponse($result);
    }
}

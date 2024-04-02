<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Contacts;

use App\Modules\Contact\Command\Relationship\Remove\ContactRemoveCommand;
use App\Modules\Contact\Command\Relationship\Remove\ContactRemoveHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

#[OA\Delete(
    path: '/users/contacts/{id}',
    description: 'Удаление пользователя из контактов, отклонение входящей/исходящей заявки в контакты',
    summary: 'Удаление пользователя из контактов, отклонение входящей/исходящей заявки в контакты',
    security: [['bearerAuth' => '{}']],
    tags: ['Users (Contacts relations)']
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
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class RemoveAction implements RequestHandlerInterface
{
    public function __construct(
        private ContactRemoveHandler $handler,
        private Validator $validator
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $command = new ContactRemoveCommand(
            userId: $identity->id,
            contactId: Route::getArgumentToInt($request, 'id')
        );

        $this->validator->validate($command);

        $uniqueId = $this->handler->handle($command);

        return new JsonDataResponse([
            'success' => 1,
            'uniqueId' => $uniqueId,
        ]);
    }
}

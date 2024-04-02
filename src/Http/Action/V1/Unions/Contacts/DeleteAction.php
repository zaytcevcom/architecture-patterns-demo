<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Unions\Contacts;

use App\Modules\Union\Command\Contact\Delete\UnionContactDeleteCommand;
use App\Modules\Union\Command\Contact\Delete\UnionContactDeleteHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataSuccessResponse;

#[OA\Delete(
    path: '/unions/{id}/contacts/{contactId}',
    description: 'Удаляет контакт
    **Коды ошибок**:<br><br>
    **1** - Доступ запрещен<br>',
    summary: 'Удаляет контакт',
    security: [['bearerAuth' => '{}']],
    tags: ['Unions (Contact)']
)]
#[OA\Parameter(
    name: 'id',
    description: 'Идентификатор объединения',
    in: 'path',
    required: true,
    schema: new OA\Schema(
        type: 'integer',
        format: 'int64'
    ),
    example: 1
)]
#[OA\Parameter(
    name: 'contactId',
    description: 'Идентификатор контакта',
    in: 'path',
    required: true,
    schema: new OA\Schema(
        type: 'integer',
        format: 'int64'
    ),
    example: 1
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class DeleteAction implements RequestHandlerInterface
{
    public function __construct(
        private UnionContactDeleteHandler $handler,
        private Validator $validator
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $command = new UnionContactDeleteCommand(
            userId: $identity->id,
            unionId: Route::getArgumentToInt($request, 'id'),
            contactId: Route::getArgumentToInt($request, 'contactId')
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataSuccessResponse();
    }
}

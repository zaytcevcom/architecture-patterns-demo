<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Contacts;

use App\Modules\Contact\Command\Relationship\Add\ContactAddCommand;
use App\Modules\Contact\Command\Relationship\Add\ContactAddHandler;
use Doctrine\DBAL\Exception;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

#[OA\Post(
    path: '/users/contacts/{id}',
    description: 'Добавление пользователя в контакты<br><br>
    **Коды ошибок**:<br>
    **1** - Пользователь не найден<br>
    **2** - Пользователи не должны совпадать<br>
    **3** - Достигнуто ограничение на максимальное кол-во контактов<br>
    **4** - Достигнут дневной лимит на максимальное кол-во контактов<br>
    ',
    summary: 'Добавление пользователя в контакты',
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
final readonly class AddAction implements RequestHandlerInterface
{
    public function __construct(
        private ContactAddHandler $handler,
        private Validator $validator
    ) {}

    /**
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $command = new ContactAddCommand(
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

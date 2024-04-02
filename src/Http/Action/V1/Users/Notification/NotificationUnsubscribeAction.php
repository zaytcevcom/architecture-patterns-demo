<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Users\Notification;

use App\Modules\Contact\Command\Notification\Unsubscribe\ContactNotificationUnsubscribeCommand;
use App\Modules\Contact\Command\Notification\Unsubscribe\ContactNotificationUnsubscribeHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataSuccessResponse;

#[OA\Delete(
    path: '/users/{id}/notifications',
    description: 'Отписка от уведомлений пользователя<br><br>
    **Коды ошибок**:<br>
    **1** - Пользователь не найден<br>
    ',
    summary: 'Отписка от уведомлений пользователя',
    security: [['bearerAuth' => '{}']],
    tags: ['Users (Notifications)']
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
final readonly class NotificationUnsubscribeAction implements RequestHandlerInterface
{
    public function __construct(
        private ContactNotificationUnsubscribeHandler $handler,
        private Validator $validator
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $command = new ContactNotificationUnsubscribeCommand(
            userId: $identity->id,
            contactId: Route::getArgumentToInt($request, 'id')
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataSuccessResponse();
    }
}

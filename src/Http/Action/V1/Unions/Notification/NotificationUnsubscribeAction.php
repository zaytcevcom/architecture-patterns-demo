<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Unions\Notification;

use App\Modules\Union\Command\Notification\Unsubscribe\UnionNotificationUnsubscribeCommand;
use App\Modules\Union\Command\Notification\Unsubscribe\UnionNotificationUnsubscribeHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataSuccessResponse;

#[OA\Delete(
    path: '/unions/{id}/notifications',
    description: 'Отписка от уведомлений объединения<br><br>
    **Коды ошибок**:<br>
    **1** - Объединение не найден<br>
    ',
    summary: 'Отписка от уведомлений объединения',
    security: [['bearerAuth' => '{}']],
    tags: ['Unions (Notifications)']
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
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class NotificationUnsubscribeAction implements RequestHandlerInterface
{
    public function __construct(
        private UnionNotificationUnsubscribeHandler $handler,
        private Validator $validator
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $command = new UnionNotificationUnsubscribeCommand(
            userId: $identity->id,
            unionId: Route::getArgumentToInt($request, 'id')
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataSuccessResponse();
    }
}

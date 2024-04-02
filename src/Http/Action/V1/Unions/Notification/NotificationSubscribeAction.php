<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Unions\Notification;

use App\Modules\Union\Command\Notification\Subscribe\UnionNotificationSubscribeCommand;
use App\Modules\Union\Command\Notification\Subscribe\UnionNotificationSubscribeHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataSuccessResponse;

#[OA\Post(
    path: '/unions/{id}/notifications',
    description: 'Подписка на уведомления от объединения<br><br>
    **Коды ошибок**:<br>
    **1** - Объединение не найдено<br>
    **2** - Достигнуто ограничение на максимальное кол-во подписок<br>
    **3** - Достигнут дневной лимит на максимальное кол-во подписок<br>
    **4** - Доступ запрещен<br>
    ',
    summary: 'Подписка на уведомления от объединения',
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
final readonly class NotificationSubscribeAction implements RequestHandlerInterface
{
    public function __construct(
        private UnionNotificationSubscribeHandler $handler,
        private Validator $validator
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $command = new UnionNotificationSubscribeCommand(
            userId: $identity->id,
            unionId: Route::getArgumentToInt($request, 'id')
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataSuccessResponse();
    }
}

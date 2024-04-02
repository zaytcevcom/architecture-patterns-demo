<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Unions;

use App\Modules\Union\Command\Leave\UnionLeaveCommand;
use App\Modules\Union\Command\Leave\UnionLeaveHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataSuccessResponse;

#[OA\Post(
    path: '/unions/{id}/leave',
    description: 'Выход/отклонение приглашения объединения<br><br>
    **Коды ошибок**:<br>
    **1** - Объединение не найдено<br>
    ',
    summary: 'Выход/отклонение приглашения объединения',
    security: [['bearerAuth' => '{}']],
    tags: ['Unions']
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
final readonly class LeaveAction implements RequestHandlerInterface
{
    public function __construct(
        private UnionLeaveHandler $handler,
        private Validator $validator
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $command = new UnionLeaveCommand(
            userId: $identity->id,
            unionId: Route::getArgumentToInt($request, 'id')
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataSuccessResponse();
    }
}

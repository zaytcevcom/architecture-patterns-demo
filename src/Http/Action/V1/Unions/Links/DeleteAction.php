<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Unions\Links;

use App\Modules\Union\Command\Link\Delete\UnionLinkDeleteCommand;
use App\Modules\Union\Command\Link\Delete\UnionLinkDeleteHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataSuccessResponse;

#[OA\Delete(
    path: '/unions/{id}/links/{linkId}',
    description: 'Удаляет ссылку
    **Коды ошибок**:<br><br>
    **1** - Доступ запрещен<br>',
    summary: 'Удаляет ссылку',
    security: [['bearerAuth' => '{}']],
    tags: ['Unions (Link)']
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
    name: 'linkId',
    description: 'Идентификатор ссылки',
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
        private UnionLinkDeleteHandler $handler,
        private Validator $validator
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $command = new UnionLinkDeleteCommand(
            userId: $identity->id,
            unionId: Route::getArgumentToInt($request, 'id'),
            linkId: Route::getArgumentToInt($request, 'linkId')
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataSuccessResponse();
    }
}

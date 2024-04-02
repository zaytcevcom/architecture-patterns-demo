<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Audios\Audios\User;

use App\Modules\Audio\Command\Audio\Remove\AudioRemoveCommand;
use App\Modules\Audio\Command\Audio\Remove\AudioRemoveHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

#[OA\Delete(
    path: '/users/{id}/audios/{audioId}',
    description: 'Удаление аудиозаписи из списка аудиозаписей пользователя',
    summary: 'Удаление аудиозаписи из списка аудиозаписей пользователя',
    security: [['bearerAuth' => '{}']],
    tags: ['Audios (User)']
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
    example: 1
)]
#[OA\Parameter(
    name: 'audioId',
    description: 'Идентификатор аудиозаписи',
    in: 'path',
    required: true,
    schema: new OA\Schema(
        type: 'integer',
        format: 'int64'
    ),
    example: 1
)]
#[OA\Response(
    response: 200,
    description: 'Successful operation'
)]
final readonly class UserRemoveAudioAction implements RequestHandlerInterface
{
    public function __construct(
        private AudioRemoveHandler $handler,
        private Validator $validator
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $userId = Route::getArgumentToInt($request, 'id');

        if ($identity->id !== $userId) {
            throw new DomainExceptionModule(
                module: 'audio',
                message: 'error.audio.permission_denied',
                code: 1
            );
        }

        $command = new AudioRemoveCommand(
            userId: $identity->id,
            audioId: Route::getArgumentToInt($request, 'audioId')
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataResponse([
            'success' => 1,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Audios\Audios;

use App\Modules\Audio\Command\Audio\AudioListen\AudioListenCommand;
use App\Modules\Audio\Command\Audio\AudioListen\AudioListenHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataSuccessResponse;

#[OA\Post(
    path: '/audios/{id}/listen',
    description: 'Добавляет прослушивание на аудиозапись',
    summary: 'Добавляет прослушивание на аудиозапись',
    security: [['bearerAuth' => '{}']],
    tags: ['Audios']
)]
#[OA\Parameter(
    name: 'id',
    description: 'Идентификатор аудиозаписи',
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
final readonly class ListenAction implements RequestHandlerInterface
{
    public function __construct(
        private AudioListenHandler $handler,
        private Validator $validator
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $command = new AudioListenCommand(
            audioId: Route::getArgumentToInt($request, 'id'),
            userId: $identity->id,
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataSuccessResponse();
    }
}

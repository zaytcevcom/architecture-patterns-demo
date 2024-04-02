<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Audios\AudioPlaylists\User;

use App\Modules\Audio\Command\AudioPlaylist\Add\AudioPlaylistAddCommand;
use App\Modules\Audio\Command\AudioPlaylist\Add\AudioPlaylistAddHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Helpers\OpenApi\ResponseSuccessful;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

#[OA\Post(
    path: '/users/{id}/audio-playlists/{playlistId}',
    description: 'Добавление плейлиста в список плейлистов пользователя<br><br>
    **Коды ошибок**:<br>
    **1** - Доступ запрещен<br>
    **2** - Достигнуто ограничение на максимальное кол-во плейлистов<br>
    **3** - Достигнут дневной лимит на максимальное кол-во плейлистов<br>',
    summary: 'Добавление плейлиста в список плейлистов пользователя',
    security: [['bearerAuth' => '{}']],
    tags: ['Audios playlists (User)'],
    responses: [new ResponseSuccessful()]
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
    name: 'playlistId',
    description: 'Идентификатор плейлиста',
    in: 'path',
    required: true,
    schema: new OA\Schema(
        type: 'integer',
        format: 'int64'
    ),
    example: 1
)]
final readonly class UserAddAudioPlaylistAction implements RequestHandlerInterface
{
    public function __construct(
        private AudioPlaylistAddHandler $handler,
        private Validator $validator
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $userId = Route::getArgumentToInt($request, 'id');

        if ($identity->id !== $userId) {
            throw new DomainExceptionModule(
                module: 'audio',
                message: 'error.audio_playlist.permission_denied',
                code: 1
            );
        }

        $command = new AudioPlaylistAddCommand(
            userId: $identity->id,
            playlistId: Route::getArgumentToInt($request, 'playlistId')
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataResponse([
            'success' => 1,
        ]);
    }
}

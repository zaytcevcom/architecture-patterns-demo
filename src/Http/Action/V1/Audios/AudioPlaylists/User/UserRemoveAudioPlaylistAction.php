<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Audios\AudioPlaylists\User;

use App\Modules\Audio\Command\AudioPlaylist\Remove\AudioPlaylistRemoveCommand;
use App\Modules\Audio\Command\AudioPlaylist\Remove\AudioPlaylistRemoveHandler;
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

#[OA\Delete(
    path: '/users/{id}/audio-playlists/{playlistId}',
    description: 'Удаление плейлиста из списка плейлистов пользователя',
    summary: 'Удаление плейлиста из списка плейлистов пользователя',
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
final readonly class UserRemoveAudioPlaylistAction implements RequestHandlerInterface
{
    public function __construct(
        private AudioPlaylistRemoveHandler $handler,
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

        $command = new AudioPlaylistRemoveCommand(
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

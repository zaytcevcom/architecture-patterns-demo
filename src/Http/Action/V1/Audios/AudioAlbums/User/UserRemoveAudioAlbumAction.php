<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Audios\AudioAlbums\User;

use App\Modules\Audio\Command\AudioAlbum\Remove\AudioAlbumRemoveCommand;
use App\Modules\Audio\Command\AudioAlbum\Remove\AudioAlbumRemoveHandler;
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
    path: '/users/{id}/audio-albums/{albumId}',
    description: 'Удаление аудио-альбома из списка аудио-альбомов пользователя',
    summary: 'Удаление аудио-альбома из списка аудио-альбомов пользователя',
    security: [['bearerAuth' => '{}']],
    tags: ['Audios albums (User)'],
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
    name: 'albumId',
    description: 'Идентификатор аудио-альбома',
    in: 'path',
    required: true,
    schema: new OA\Schema(
        type: 'integer',
        format: 'int64'
    ),
    example: 1
)]
final readonly class UserRemoveAudioAlbumAction implements RequestHandlerInterface
{
    public function __construct(
        private AudioAlbumRemoveHandler $handler,
        private Validator $validator
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $userId = Route::getArgumentToInt($request, 'id');

        if ($identity->id !== $userId) {
            throw new DomainExceptionModule(
                module: 'audio',
                message: 'error.audio_album.permission_denied',
                code: 1
            );
        }

        $command = new AudioAlbumRemoveCommand(
            userId: $identity->id,
            albumId: Route::getArgumentToInt($request, 'albumId')
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataResponse([
            'success' => 1,
        ]);
    }
}

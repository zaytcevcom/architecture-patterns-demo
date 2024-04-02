<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Audios\AudioPlaylists;

use App\Http\Action\Unifier\Audio\AudioPlaylistUnifier;
use App\Modules\Audio\Query\AudioPlaylist\GetById\AudioPlaylistGetByIdFetcher;
use App\Modules\Audio\Query\AudioPlaylist\GetById\AudioPlaylistGetByIdQuery;
use App\Modules\Audio\Service\AudioPlaylistRealtimeNotifier;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Realtime\Realtime;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Helpers\OpenApi\ResponseSuccessful;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

use function App\Components\env;

#[OA\Get(
    path: '/audio-playlists/{id}',
    description: 'Информация о плейлисте по его идентификатору',
    summary: 'Информация о плейлисте по его идентификатору',
    security: [['bearerAuth' => '{}']],
    tags: ['Audios playlists'],
    responses: [new ResponseSuccessful()]
)]
#[OA\Parameter(
    name: 'id',
    description: 'Идентификатор плейлиста',
    in: 'path',
    required: true,
    schema: new OA\Schema(
        type: 'integer',
        format: 'int64'
    ),
    example: 1
)]
final readonly class GetByIdAction implements RequestHandlerInterface
{
    public function __construct(
        private AudioPlaylistGetByIdFetcher $fetcher,
        private Validator $validator,
        private AudioPlaylistUnifier $unifier,
        private Realtime $realtime,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $audioPlaylistId = Route::getArgumentToInt($request, 'id');

        $query = new AudioPlaylistGetByIdQuery(
            id: $audioPlaylistId
        );

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query);

        $channel = AudioPlaylistRealtimeNotifier::getChannelName($audioPlaylistId);

        return new JsonDataResponse(
            array_merge(
                $this->unifier->unifyOne($identity->id, $result),
                [
                    'realtime' => [
                        'connection'    => env('CENTRIFUGO_WS'),
                        'token'         => $this->realtime->generateConnectionToken((string)$identity->id, time() + 7 * 24 * 3600),
                        'channel'       => $channel,
                        'channelToken'  => $this->realtime->generateSubscriptionToken((string)$identity->id, $channel, time() + 2 * 24 * 3600),
                    ],
                ]
            )
        );
    }
}

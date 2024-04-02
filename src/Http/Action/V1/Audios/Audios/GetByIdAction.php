<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Audios\Audios;

use App\Http\Action\Unifier\Audio\AudioUnifier;
use App\Modules\Audio\Query\Audio\GetById\AudioGetByIdFetcher;
use App\Modules\Audio\Query\Audio\GetById\AudioGetByIdQuery;
use App\Modules\Audio\Service\AudioRealtimeNotifier;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Realtime\Realtime;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

use function App\Components\env;

#[OA\Get(
    path: '/audios/{id}',
    description: 'Информация об аудиозаписи по её идентификатору',
    summary: 'Информация об аудиозаписи по её идентификатору',
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
    example: 1
)]
#[OA\Response(
    response: 200,
    description: 'Successful operation'
)]
final readonly class GetByIdAction implements RequestHandlerInterface
{
    public function __construct(
        private AudioGetByIdFetcher $fetcher,
        private Validator $validator,
        private AudioUnifier $unifier,
        private Realtime $realtime,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $audioId = Route::getArgumentToInt($request, 'id');

        $query = new AudioGetByIdQuery(
            id: $audioId
        );

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query);

        $channel = AudioRealtimeNotifier::getChannelName($audioId);

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

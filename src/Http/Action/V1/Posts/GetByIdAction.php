<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Posts;

use App\Http\Action\Unifier\Post\PostUnifier;
use App\Modules\Post\Entity\Post\PostRepository;
use App\Modules\Post\Service\PostRealtimeNotifier;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Realtime\Realtime;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

use function App\Components\env;

#[OA\Get(
    path: '/posts/{id}',
    description: 'Получение информации о посте по его идентификатору',
    summary: 'Получение информации о посте по его идентификатору',
    security: [['bearerAuth' => '{}']],
    tags: ['Posts']
)]
#[OA\Parameter(
    name: 'id',
    description: 'Идентификатор поста',
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
        private PostRepository $postRepository,
        private PostUnifier $unifier,
        private Realtime $realtime,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::findIdentity($request);

        $post = $this->postRepository->getById(
            id: Route::getArgumentToInt($request, 'id'),
        );

        $channel = PostRealtimeNotifier::getChannelName($post->getId());

        return new JsonDataResponse(
            array_merge(
                $this->unifier->unifyOne($identity?->id, $post->toArray()),
                [
                    'realtime' => [
                        'connection'    => env('CENTRIFUGO_WS'),
                        'token'         => $this->realtime->generateConnectionToken((string)$identity?->id, time() + 7 * 24 * 3600),
                        'channel'       => $channel,
                        'channelToken'  => $this->realtime->generateSubscriptionToken((string)$identity?->id, $channel, time() + 2 * 24 * 3600),
                    ],
                ]
            )
        );
    }
}

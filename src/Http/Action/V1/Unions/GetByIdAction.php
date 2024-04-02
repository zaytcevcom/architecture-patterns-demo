<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Unions;

use App\Http\Action\Unifier\Union\UnionUnifier;
use App\Modules\Union\Query\Union\GetById\UnionGetByIdFetcher;
use App\Modules\Union\Query\Union\GetById\UnionGetByIdQuery;
use App\Modules\Union\Service\UnionRealtimeNotifier;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Realtime\Realtime;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

use function App\Components\env;

#[OA\Get(
    path: '/unions/{id}',
    description: 'Получение информации об объединении по его идентификатору',
    summary: 'Получение информации об объединении по его идентификатору',
    security: [['bearerAuth' => '{}']],
    tags: ['Unions'],
)]
#[OA\Parameter(
    name: 'id',
    description: 'Идентификатор объединения',
    in: 'path',
    required: true,
    schema: new OA\Schema(
        type: 'integer'
    ),
    example: 1
)]
#[OA\Parameter(
    name: 'fields',
    description: 'Доп. поля (dates, union, counters, country, city, category, subcategory, member, notification, permissions, sections)',
    in: 'query',
    required: false,
    schema: new OA\Schema(
        type: 'string'
    ),
    example: null
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class GetByIdAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private UnionGetByIdFetcher $fetcher,
        private Validator $validator,
        private UnionUnifier $unifier,
        private Realtime $realtime,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::findIdentity($request);

        $unionId = Route::getArgumentToInt($request, 'id');

        $query = $this->denormalizer->denormalizeQuery(
            array_merge(
                $request->getQueryParams(),
                ['id' => $unionId]
            ),
            UnionGetByIdQuery::class
        );

        $this->validator->validate($query);

        /**
         * @var array{
         *     id:int,
         * } $result
         */
        $result = $this->fetcher->fetch($query);

        $channel = UnionRealtimeNotifier::getChannelName($unionId);

        return new JsonDataResponse(
            array_merge(
                $this->unifier->unifyOne($identity?->id, $result, $query->fields),
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

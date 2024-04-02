<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Audios\Audios;

use App\Modules\Audio\Query\Audio\GetLyrics\GetLyricsFetcher;
use App\Modules\Audio\Query\Audio\GetLyrics\GetLyricsQuery;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

#[OA\Get(
    path: '/audios/{id}/lyrics',
    description: 'Текст песни аудиозаписи',
    summary: 'Текст песни аудиозаписи',
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
final readonly class GetLyricsAction implements RequestHandlerInterface
{
    public function __construct(
        private GetLyricsFetcher $fetcher,
        private Validator $validator,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        Authenticate::getIdentity($request);

        $query = new GetLyricsQuery(
            id: Route::getArgumentToInt($request, 'id')
        );

        $this->validator->validate($query);

        return new JsonDataResponse(
            $this->fetcher->fetch($query)
        );
    }
}

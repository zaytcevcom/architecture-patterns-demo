<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Users;

use App\Modules\Identity\Query\GetTotalCount\GetTotalCountFetcher;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

#[OA\Get(
    path: '/users/total',
    description: 'Общее количество пользователей',
    summary: 'Общее количество пользователей',
    security: [['bearerAuth' => '{}']],
    tags: ['Users']
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class GetTotalCountAction implements RequestHandlerInterface
{
    public function __construct(
        private GetTotalCountFetcher $fetcher,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonDataResponse([
            'count' => $this->fetcher->fetch(),
        ]);
    }
}

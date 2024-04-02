<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Identity;

use App\Modules\Identity\Command\SetOnline\IdentitySetOnlineHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataSuccessResponse;

#[OA\Post(
    path: '/identity/online',
    description: 'Пометить статус пользователя как online',
    summary: 'Пометить статус пользователя как online',
    tags: ['Identity']
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class OnlineAction implements RequestHandlerInterface
{
    public function __construct(
        private IdentitySetOnlineHandler $handler,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $this->handler->handle($identity->id);

        return new JsonDataSuccessResponse();
    }
}

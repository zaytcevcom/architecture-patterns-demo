<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Identity;

use App\Modules\Identity\Query\GetSignupMethods\IdentityGetSignupMethodsFetcher;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

#[OA\Get(
    path: '/identity/signup/methods',
    description: 'Получение доступных способов регистрации',
    summary: 'Получение доступных способов регистрации',
    tags: ['Identity (Signup)']
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class GetSignupMethodsAction implements RequestHandlerInterface
{
    public function __construct(
        private IdentityGetSignupMethodsFetcher $fetcher
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $rows = $this->fetcher->fetch();

        return new JsonDataResponse(
            $rows
        );
    }
}

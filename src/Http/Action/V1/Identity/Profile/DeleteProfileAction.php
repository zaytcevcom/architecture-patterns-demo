<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Identity\Profile;

use App\Modules\Identity\Command\Delete\IdentityDeleteCommand;
use App\Modules\Identity\Command\Delete\IdentityDeleteHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataSuccessResponse;

#[OA\Delete(
    path: '/identity/profile',
    description: 'Удаление профиля пользователя',
    summary: 'Удаление профиля пользователя',
    security: [['bearerAuth' => '{}']],
    tags: ['Identity']
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class DeleteProfileAction implements RequestHandlerInterface
{
    public function __construct(
        private IdentityDeleteHandler $handler,
        private Validator $validator,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $command = new IdentityDeleteCommand($identity->id);

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataSuccessResponse();
    }
}

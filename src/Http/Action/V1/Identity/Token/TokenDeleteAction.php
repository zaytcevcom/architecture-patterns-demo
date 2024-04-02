<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Identity\Token;

use App\Modules\OAuth\Command\LogOut\LogOutCommand;
use App\Modules\OAuth\Command\LogOut\LogOutHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Response\JsonDataSuccessResponse;

#[OA\Delete(
    path: '/identity/token',
    description: 'Удаление refresh_token',
    summary: 'Выход',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'refreshToken',
                    type: 'string',
                    example: 'ae25f64dce61319a120c72d2c5d36f53aec28a64162b07e271db6ad832667b7318fd9f8e4c8b18c5'
                ),
            ]
        )
    ),
    tags: ['Identity']
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class TokenDeleteAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private Validator $validator,
        private LogOutHandler $logOutHandler
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $command = $this->denormalizer->denormalize($request->getParsedBody(), LogOutCommand::class);

        $this->validator->validate($command);

        $this->logOutHandler->handle($command);

        return new JsonDataSuccessResponse();
    }
}

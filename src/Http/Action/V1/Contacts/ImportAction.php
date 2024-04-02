<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Contacts;

use App\Modules\Contact\Command\Import\ContactImportCommand;
use App\Modules\Contact\Command\Import\ContactImportHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataSuccessResponse;

#[OA\Post(
    path: '/users/import',
    description: 'Импорт контактов пользователя',
    summary: 'Импорт контактов пользователя',
    security: [['bearerAuth' => '{}']],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(
                                property: 'phones',
                                type: 'array',
                                items: new OA\Items(),
                                example: ['78888888888', '79999999999']
                            ),
                            new OA\Property(
                                property: 'firstName',
                                type: 'string',
                                example: 'Имя'
                            ),
                            new OA\Property(
                                property: 'lastName',
                                type: 'string',
                                example: 'Фамилия'
                            ),
                        ],
                        type: 'object'
                    )
                ),
            ],
        )
    ),
    tags: ['Users (Contacts Import)']
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class ImportAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private ContactImportHandler $handler,
        private Validator $validator,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $command = $this->denormalizer->denormalize(
            array_merge(
                (array)$request->getParsedBody(),
                ['userId' => $identity->id]
            ),
            ContactImportCommand::class
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataSuccessResponse();
    }
}

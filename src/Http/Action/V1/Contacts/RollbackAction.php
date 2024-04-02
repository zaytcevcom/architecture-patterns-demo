<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Contacts;

use App\Modules\Contact\Command\Relationship\Rollback\ContactRollbackCommand;
use App\Modules\Contact\Command\Relationship\Rollback\ContactRollbackHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataSuccessResponse;

#[OA\Post(
    path: '/users/contacts/rollback',
    description: 'Отменяет добавление/отклонение пользователя<br><br>
    **Коды ошибок**:<br>
    **1** - Истекло время в течении которого можно отменить действие
    ',
    summary: 'Отменяет добавление/отклонение пользователя',
    security: [['bearerAuth' => '{}']],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'uniqueId',
                    type: 'string',
                    example: 'c92e74f0-0bb6-4e8c-a276-e62100e8d09b'
                ),
            ]
        )
    ),
    tags: ['Users (Contacts relations)']
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class RollbackAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private ContactRollbackHandler $handler,
        private Validator $validator
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $command = $this->denormalizer->denormalize(
            array_merge(
                (array)$request->getParsedBody(),
                ['userId' => $identity->id]
            ),
            ContactRollbackCommand::class
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataSuccessResponse();
    }
}

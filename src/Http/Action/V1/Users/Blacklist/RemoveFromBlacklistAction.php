<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Users\Blacklist;

use App\Modules\Contact\Command\Blacklist\Remove\ContactRemoveFromBlacklistCommand;
use App\Modules\Contact\Command\Blacklist\Remove\ContactRemoveFromBlacklistHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataSuccessResponse;

#[OA\Delete(
    path: '/identity/blacklists',
    description: 'Удаление пользователя из черного списка<br><br>
    **Коды ошибок**:<br>
    **1** - Пользователь не найден<br>
    **2** - Пользователи не должны совпадать
    ',
    summary: 'Удаление пользователя из черного списка',
    security: [['bearerAuth' => '{}']],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'userId',
                    type: 'integer',
                    example: 1,
                ),
            ]
        )
    ),
    tags: ['Identity (Blacklists)']
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class RemoveFromBlacklistAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private ContactRemoveFromBlacklistHandler $handler,
        private Validator $validator
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $command = $this->denormalizer->denormalize(
            array_merge((array)$request->getParsedBody(), [
                'sourceId' => $identity->id,
            ]),
            ContactRemoveFromBlacklistCommand::class
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataSuccessResponse();
    }
}

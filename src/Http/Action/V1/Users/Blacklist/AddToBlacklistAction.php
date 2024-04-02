<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Users\Blacklist;

use App\Modules\Contact\Command\Blacklist\Add\ContactAddToBlacklistCommand;
use App\Modules\Contact\Command\Blacklist\Add\ContactAddToBlacklistHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataSuccessResponse;

#[OA\Post(
    path: '/identity/blacklists',
    description: 'Добавление пользователя в черный список<br><br>
    **Коды ошибок**:<br>
    **1** - Пользователь не найден<br>
    **2** - Пользователи не должны совпадать
    ',
    summary: 'Добавление пользователя в черный список',
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
    tags: ['Identity (Blacklists)'],
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class AddToBlacklistAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private ContactAddToBlacklistHandler $handler,
        private Validator $validator
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $command = $this->denormalizer->denormalize(
            array_merge((array)$request->getParsedBody(), [
                'sourceId' => $identity->id,
            ]),
            ContactAddToBlacklistCommand::class
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataSuccessResponse();
    }
}

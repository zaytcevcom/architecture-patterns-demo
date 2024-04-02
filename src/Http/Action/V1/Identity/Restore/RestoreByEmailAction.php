<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Identity\Restore;

use App\Http\Action\Unifier\User\UserUnifier;
use App\Modules\Identity\Command\Restore\Email\IdentityRestoreByEmailCommand;
use App\Modules\Identity\Command\Restore\Email\IdentityRestoreByEmailHandler;
use App\Modules\Identity\Entity\User\User;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

#[OA\Post(
    path: '/identity/restore/email',
    description: 'Шаг 1. Восстановление пароля по email.<br><br>
        В ответе будут получены:<br>
        - **uniqueId** - необходимо передать в метод /identity/restore/code<br>
        - **user** - информация о профиле пользователя<br><br>
        **Коды ошибок**<br>
        **1** - Пользователь не найден<br>
        **2** - Превышен лимит на кол-во попыток<br>
    ',
    summary: 'Шаг 1. Восстановление пароля по email',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'firstName',
                    type: 'string',
                    example: 'Константин'
                ),
                new OA\Property(
                    property: 'lastName',
                    type: 'string',
                    example: 'Зайцев'
                ),
                new OA\Property(
                    property: 'email',
                    type: 'string',
                    example: 'zaydisk@yandex.ru'
                ),
            ]
        )
    ),
    tags: ['Identity (Restore)']
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class RestoreByEmailAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private IdentityRestoreByEmailHandler $handler,
        private Validator $validator,
        private UserUnifier $unifier
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $command = $this->denormalizer->denormalize($request->getParsedBody(), IdentityRestoreByEmailCommand::class);

        $this->validator->validate($command);

        /**
         * @var array{
         *     uniqueId:string,
         *     user:User,
         * } $result
         */
        $result = $this->handler->handle($command);

        return new JsonDataResponse([
            'uniqueId' => $result['uniqueId'],
            'user' => $this->unifier->unify(null, [$result['user']->toArray()])[0],
        ]);
    }
}

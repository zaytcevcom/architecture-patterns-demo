<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Identity\SignupByPhone;

use App\Modules\Identity\Command\SignupByPhone\Confirm\IdentitySignupByPhoneConfirmCommand;
use App\Modules\Identity\Command\SignupByPhone\Confirm\IdentitySignupByPhoneConfirmHandler;
use App\Modules\OAuth\Entity\Client;
use App\Modules\OAuth\Generator\BearerTokenGenerator;
use App\Modules\Union\Command\Join\UnionJoinCommand;
use App\Modules\Union\Command\Join\UnionJoinHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

#[OA\Post(
    path: '/identity/signup/phone/confirm',
    description: 'Шаг 3. Завершение регистрации по sms.<br><br>
        В случае успеха вернется ```id``` пользователя и объект ```token```<br><br>
        **Коды ошибок**<br>
        **1** - Информация о начале регистрации не найдена<br>
        **2** - Капча не решена<br>
        **3** - Неверный код подтверждения<br>
    ',
    summary: 'Шаг 3. Завершение регистрации по sms',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'uniqueId',
                    type: 'string',
                    example: '561c5132-ef7f-4dc1-9c2d-af7032daec4e'
                ),
                new OA\Property(
                    property: 'code',
                    type: 'string',
                    example: '03283'
                ),
                new OA\Property(
                    property: 'clientId',
                    type: 'string',
                    example: '5'
                ),
            ]
        )
    ),
    tags: ['Identity (Signup by phone)']
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class ConfirmAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private IdentitySignupByPhoneConfirmHandler $handler,
        private UnionJoinHandler $unionJoinHandler,
        private Validator $validator,
        private BearerTokenGenerator $tokenBearerGenerator
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $command = $this->denormalizer->denormalize(
            data: $request->getParsedBody(),
            type: IdentitySignupByPhoneConfirmCommand::class
        );

        $this->validator->validate($command);

        $user = $this->handler->handle($command);

        $this->unionJoin($user->getId());

        return new JsonDataResponse([
            'id' => $user->getId(),
            'token' => $this->generateToken($command->clientId, $user->getId()),
        ]);
    }

    private function generateToken(string $clientId, int $userId): array
    {
        return $this->tokenBearerGenerator->generate(
            client: new Client(
                identifier: $clientId,
                name: 'SERVER',
                redirectUri: 'default'
            ),
            userId: (string)$userId
        );
    }

    private function unionJoin(int $userId): void
    {
        $this->unionJoinHandler->handle(
            new UnionJoinCommand(
                userId: $userId,
                unionId: 1
            )
        );
    }
}

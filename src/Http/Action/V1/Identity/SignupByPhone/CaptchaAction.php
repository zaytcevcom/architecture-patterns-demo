<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Identity\SignupByPhone;

use App\Modules\Identity\Command\SignupByPhone\Captcha\IdentitySignupByPhoneCaptchaCommand;
use App\Modules\Identity\Command\SignupByPhone\Captcha\IdentitySignupByPhoneCaptchaHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

#[OA\Post(
    path: '/identity/signup/phone/captcha',
    description: 'Шаг 2. Проверка капчи.<br><br>
        В случае успеха будет получен ответ:<br>
        - **success** = 1<br>
        - **interval** - кол-во секунд, через которые можно снова отправить код подтверждения<br><br>
        Иначе:<br>
        - **success** = 0<br>
        - **captcha** - данные для ввода капчи<br><br>
        **Коды ошибок**<br>
        **1** - Информация о начале регистрации не найдена<br>
    ',
    summary: 'Шаг 2. Проверка капчи',
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
                    example: '0328303283032830328303283'
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
final readonly class CaptchaAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private IdentitySignupByPhoneCaptchaHandler $handler,
        private Validator $validator
    ) {}

    /** @throws ExceptionInterface */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $command = $this->denormalizer->denormalize(
            data: $request->getParsedBody(),
            type: IdentitySignupByPhoneCaptchaCommand::class
        );

        $this->validator->validate($command);

        $data = $this->handler->handle($command);

        return new JsonDataResponse($data);
    }
}

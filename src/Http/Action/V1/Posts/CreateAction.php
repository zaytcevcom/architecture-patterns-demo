<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Posts;

use App\Http\Action\Unifier\Post\PostUnifier;
use App\Modules\Post\Command\Post\Create\PostCreateCommand;
use App\Modules\Post\Command\Post\Create\PostCreateHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

#[OA\Post(
    path: '/posts',
    description: 'Создание поста<br><br>
    **Коды ошибок**:<br>
    **1** - Доступ запрещен<br>
    **2** - Достигнуто ограничение на максимальное кол-во постов<br>
    **3** - Достигнут дневной лимит на максимальное кол-во постов<br>
    **4** - Дублирующийся пост<br>',
    summary: 'Создание поста',
    security: [['bearerAuth' => '{}']],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'unionId',
                    type: 'integer',
                    example: null,
                ),
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    example: 'Новый пост!'
                ),
                new OA\Property(
                    property: 'photoIds',
                    type: 'array',
                    items: new OA\Items(),
                    example: [100001, 100002, 100003]
                ),
                new OA\Property(
                    property: 'audioIds',
                    type: 'array',
                    items: new OA\Items(),
                    example: [200001, 200002, 200003]
                ),
                new OA\Property(
                    property: 'videoIds',
                    type: 'array',
                    items: new OA\Items(),
                    example: null
                ),
                new OA\Property(
                    property: 'flowId',
                    type: 'integer',
                    example: null,
                ),
                new OA\Property(
                    property: 'time',
                    type: 'integer',
                    example: null,
                ),
                new OA\Property(
                    property: 'uniqueTime',
                    type: 'integer',
                    example: null,
                ),
                new OA\Property(
                    property: 'closeComments',
                    type: 'boolean',
                    example: false
                ),
                new OA\Property(
                    property: 'contactsOnly',
                    type: 'boolean',
                    example: false
                ),
                new OA\Property(
                    property: 'socialIds',
                    type: 'array',
                    items: new OA\Items(),
                    example: [1, 2, 3]
                ),
            ]
        )
    ),
    tags: ['Posts']
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class CreateAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private PostCreateHandler $handler,
        private Validator $validator,
        private PostUnifier $unifier
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $command = $this->denormalizer->denormalize(
            array_merge((array)$request->getParsedBody(), [
                'userId' => $identity->id,
            ]),
            PostCreateCommand::class
        );

        $this->validator->validate($command);

        $post = $this->handler->handle($command);

        return new JsonDataResponse($this->unifier->unifyOne($identity->id, $post->toArray()));
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Posts\Comments;

use App\Http\Action\Unifier\Photo\PhotoUnifier;
use App\Modules\Post\Command\PostComment\PhotoSave\PostCommentPhotoSaveCommand;
use App\Modules\Post\Command\PostComment\PhotoSave\PostCommentPhotoSaveHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

#[OA\Post(
    path: '/posts/comments/photos',
    description: 'Сохранение загруженной фотографии для комментария к записи',
    summary: 'Сохранение загруженной фотографии для комментария к записи',
    security: [['bearerAuth' => '{}']],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'host',
                    type: 'string',
                    example: 'https://host.com'
                ),
                new OA\Property(
                    property: 'fileId',
                    type: 'string',
                    example: '1659255729.f2a3cf7ef6cd7808c493571ccf40680'
                ),
            ]
        )
    ),
    tags: ['Posts (Comments)']
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class SavePhotoAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private PostCommentPhotoSaveHandler $handler,
        private Validator $validator,
        private PhotoUnifier $unifier
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $command = $this->denormalizer->denormalize(
            array_merge(
                (array)$request->getParsedBody(),
                [
                    'userId' => $identity->id,
                ]
            ),
            PostCommentPhotoSaveCommand::class
        );

        $this->validator->validate($command);

        $photo = $this->handler->handle($command);

        return new JsonDataResponse($this->unifier->unifyOne($identity->id, $photo->toArray()));
    }
}

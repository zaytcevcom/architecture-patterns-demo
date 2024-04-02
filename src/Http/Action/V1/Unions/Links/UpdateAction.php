<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Unions\Links;

use App\Modules\Union\Command\Link\Update\UnionLinkUpdateCommand;
use App\Modules\Union\Command\Link\Update\UnionLinkUpdateHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataSuccessResponse;

#[OA\Put(
    path: '/unions/{id}/links/{linkId}',
    description: 'Редактирование ссылки',
    summary: 'Редактирование ссылки',
    security: [['bearerAuth' => '{}']],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'url',
                    type: 'string',
                    example: 'https://lo.ink/id1'
                ),
                new OA\Property(
                    property: 'title',
                    type: 'string',
                    example: 'CEO'
                ),
            ]
        )
    ),
    tags: ['Unions (Link)']
)]
#[OA\Parameter(
    name: 'id',
    description: 'Идентификатор объединения',
    in: 'path',
    required: true,
    schema: new OA\Schema(
        type: 'integer',
        format: 'int64'
    ),
    example: 1
)]
#[OA\Parameter(
    name: 'linkId',
    description: 'Идентификатор ссылки',
    in: 'path',
    required: true,
    schema: new OA\Schema(
        type: 'integer',
        format: 'int64'
    ),
    example: 1
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class UpdateAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private UnionLinkUpdateHandler $handler,
        private Validator $validator,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $command = $this->denormalizer->denormalize(
            array_merge((array)$request->getParsedBody(), [
                'userId' => $identity->id,
                'unionId' => Route::getArgumentToInt($request, 'id'),
                'linkId' => Route::getArgumentToInt($request, 'linkId'),
            ]),
            UnionLinkUpdateCommand::class
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataSuccessResponse();
    }
}

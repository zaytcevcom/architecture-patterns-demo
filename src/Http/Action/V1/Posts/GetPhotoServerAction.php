<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Posts;

use App\Modules\Post\Query\Post\GetPhotoServer\PostGetPhotoServerFetcher;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

#[OA\Get(
    path: '/posts/photo-server',
    description: 'Возвращает адрес сервера для загрузки фото в ленту пользователя или объединения.<br><br>
    На полученный адрес методом POST необходимо отправить изображение через поле upload_file и owner_id',
    summary: 'Возвращает адрес сервера для загрузки фото в ленту пользователя или объединения',
    security: [['bearerAuth' => '{}']],
    tags: ['Posts']
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class GetPhotoServerAction implements RequestHandlerInterface
{
    public function __construct(
        private PostGetPhotoServerFetcher $fetcher
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        Authenticate::getIdentity($request);

        return new JsonDataResponse(
            $this->fetcher->fetch()
        );
    }
}

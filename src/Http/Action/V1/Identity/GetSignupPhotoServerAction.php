<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Identity;

use App\Modules\Identity\Query\GetSignupPhotoServer\IdentityGetSignupPhotoServerFetcher;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

#[OA\Get(
    path: '/identity/signup/photo-server',
    description: 'Возвращает адрес сервера для загрузки фото профиля.<br><br>
    На полученный адрес методом POST необходимо отправить изображение через поле upload_file',
    summary: 'Возвращает адрес сервера для загрузки фото профиля',
    tags: ['Identity (Signup)']
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class GetSignupPhotoServerAction implements RequestHandlerInterface
{
    public function __construct(
        private IdentityGetSignupPhotoServerFetcher $fetcher
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $rows = $this->fetcher->fetch();

        return new JsonDataResponse(
            $rows
        );
    }
}

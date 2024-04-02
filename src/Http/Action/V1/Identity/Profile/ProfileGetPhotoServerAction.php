<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Identity\Profile;

use App\Modules\Identity\Query\GetPhotoServer\IdentityGetPhotoServerFetcher;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Helpers\OpenApi\ResponseSuccessful;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

#[OA\Get(
    path: '/identity/profile/photo-server',
    description: 'Возвращает адрес сервера для загрузки аватара пользователя.<br><br>
    На полученный адрес методом POST необходимо отправить изображение через поле upload_file и user_id',
    summary: 'Возвращает адрес сервера для загрузки аватара пользователя',
    security: [['bearerAuth' => '{}']],
    tags: ['Identity'],
    responses: [new ResponseSuccessful()]
)]
final readonly class ProfileGetPhotoServerAction implements RequestHandlerInterface
{
    public function __construct(
        private IdentityGetPhotoServerFetcher $fetcher
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        Authenticate::getIdentity($request);

        $rows = $this->fetcher->fetch();

        return new JsonDataResponse(
            $rows
        );
    }
}

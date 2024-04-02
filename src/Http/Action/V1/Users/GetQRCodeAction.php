<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Users;

use App\Modules\Identity\Entity\User\User;
use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Storage\Entity\StorageHostRepository;
use App\Modules\Storage\Service\StorageQRCode;
use Exception;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

#[OA\Get(
    path: '/users/{id}/qr-code',
    description: 'Получение QR-code пользователя',
    summary: 'Получение QR-code пользователя',
    security: [['bearerAuth' => '{}']],
    tags: ['Users']
)]
#[OA\Parameter(
    name: 'id',
    description: 'Идентификатор пользователя',
    in: 'path',
    required: true,
    schema: new OA\Schema(
        type: 'string'
    ),
    example: '1'
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class GetQRCodeAction implements RequestHandlerInterface
{
    public function __construct(
        private StorageQRCode $storageQRCode,
        private StorageHostRepository $storageHostRepository,
        private UserRepository $userRepository,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        Authenticate::getIdentity($request);

        $user = $this->userRepository->getById(Route::getArgumentToInt($request, 'id'));

        $storageHost = $this->storageHostRepository->getByRandom();

        $this->storageQRCode->setHost($storageHost->getHost());
        $this->storageQRCode->setApiKey($storageHost->getSecret());

        try {
            $url = $this->storageQRCode->create(
                User::getWebUrl($user->getId())
            );
        } catch (Exception) {
            $url = null;
        }

        return new JsonDataResponse([
            'url' => $url,
        ]);
    }
}

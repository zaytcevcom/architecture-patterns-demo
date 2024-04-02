<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Unions;

use App\Modules\Storage\Entity\StorageHostRepository;
use App\Modules\Storage\Service\StorageQRCode;
use App\Modules\Union\Entity\Union\UnionRepository;
use App\Modules\Union\Service\UnionSerializer;
use Exception;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

#[OA\Get(
    path: '/unions/{id}/qr-code',
    description: 'Получение QR-code объединения',
    summary: 'Получение QR-code объединения',
    security: [['bearerAuth' => '{}']],
    tags: ['Unions']
)]
#[OA\Parameter(
    name: 'id',
    description: 'Идентификатор объединения',
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
        private UnionRepository $unionRepository,
        private UnionSerializer $unionSerializer,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        Authenticate::getIdentity($request);

        $union = $this->unionRepository->getById(Route::getArgumentToInt($request, 'id'));

        $storageHost = $this->storageHostRepository->getByRandom();

        $this->storageQRCode->setHost($storageHost->getHost());
        $this->storageQRCode->setApiKey($storageHost->getSecret());

        try {
            $url = $this->storageQRCode->create(
                $this->unionSerializer->getWebUrl($union->getType(), $union->getId())
            );
        } catch (Exception) {
            $url = null;
        }

        return new JsonDataResponse([
            'url' => $url,
        ]);
    }
}

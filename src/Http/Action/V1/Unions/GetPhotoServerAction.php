<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Unions;

use App\Modules\Union\Query\Union\GetPhotoServer\UnionGetPhotoServerFetcher;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Helpers\OpenApi\ResponseSuccessful;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

#[OA\Get(
    path: '/unions/photo-server',
    description: 'Возвращает адрес сервера для загрузки аватара объединения.<br><br>
    На полученный адрес методом POST необходимо отправить изображение через поле upload_file и union_id (0 - при создании)',
    summary: 'Возвращает адрес сервера для загрузки аватара объединения',
    tags: ['Unions (Management)'],
    responses: [new ResponseSuccessful()]
)]
final readonly class GetPhotoServerAction implements RequestHandlerInterface
{
    public function __construct(
        private UnionGetPhotoServerFetcher $fetcher
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonDataResponse(
            $this->fetcher->fetch()
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Unions\Communities;

use App\Modules\Banner\Entity\Banner\Banner;
use App\Modules\Banner\Query\Banner\GetBySection\BannerGetBySectionFetcher;
use App\Modules\Banner\Query\Banner\GetBySection\BannerGetBySectionQuery;
use App\Modules\Banner\Service\BannerSerializer;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataItemsResponse;

#[OA\Get(
    path: '/communities/banners',
    description: 'Получение баннеров для раздела "Сообщества"<br><br>
    **type** - тип блока (1 - блок в виде изображения)<br>
    **itemType** - тип объекта, на который нужно перейти (1 - аудио-альбом, 2 - сообщество, 3 - место, 4 - событие)<br>
    **itemId** - идентификатор объекта, на который нужно перейти<br>
    **url** - если тип объекта не заполнен, то переход по ссылке<br><br>
    **itemType** и **url** могут одновременно отсутствовать',
    summary: 'Получение баннеров для раздела "Сообщества"',
    security: [['bearerAuth' => '{}']],
    tags: ['Communities']
)]
#[OA\Parameter(
    name: 'sort',
    description: 'Сортировка (0 - по убыванию, 1 - по возрастания)',
    in: 'query',
    required: false,
    schema: new OA\Schema(
        type: 'integer',
        format: 'int64'
    ),
    example: 0
)]
#[OA\Parameter(
    name: 'count',
    description: 'Кол-во которое необходимо получить',
    in: 'query',
    required: false,
    schema: new OA\Schema(
        type: 'integer',
        format: 'int64'
    ),
    example: 100
)]
#[OA\Parameter(
    name: 'offset',
    description: 'Смещение',
    in: 'query',
    required: false,
    schema: new OA\Schema(
        type: 'integer',
        format: 'int64'
    ),
    example: 0
)]
#[OA\Response(
    response: 200,
    description: 'Successful operation'
)]
final readonly class GetBannersAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private BannerGetBySectionFetcher $fetcher,
        private Validator $validator,
        private BannerSerializer $serializer
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        Authenticate::getIdentity($request);

        $query = $this->denormalizer->denormalizeQuery(
            array_merge(
                $request->getQueryParams(),
                ['section' => Banner::sectionCommunity()]
            ),
            BannerGetBySectionQuery::class
        );

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query);

        return new JsonDataItemsResponse(
            count: $result->count,
            items: $this->serializer->serializeItems($result->items)
        );
    }
}

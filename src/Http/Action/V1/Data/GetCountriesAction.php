<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Data;

use App\Modules\Data\Query\Country\Get\DataCountryGetFetcher;
use App\Modules\Data\Query\Country\Get\DataCountryGetQuery;
use App\Modules\Data\Service\CountrySerializer;
use Doctrine\DBAL\Exception;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\Translation\Translator;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Response\JsonDataItemsResponse;

#[OA\Get(
    path: '/data/countries',
    description: 'Получение информации о странах',
    summary: 'Получение информации о странах',
    tags: ['Data']
)]
#[OA\Parameter(
    name: 'search',
    description: 'Поисковый запрос',
    in: 'query',
    required: false,
    schema: new OA\Schema(
        type: 'string'
    ),
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
    example: '100'
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
    example: '0'
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class GetCountriesAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private DataCountryGetFetcher $fetcher,
        private Validator $validator,
        private CountrySerializer $serializer,
        private Translator $translator
    ) {}

    /**
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $query = $this->denormalizer->denormalizeQuery(
            array_merge(
                $request->getQueryParams(),
                ['locale' => $this->translator->getLocale()]
            ),
            DataCountryGetQuery::class
        );

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query);

        return new JsonDataItemsResponse(
            count: $result->count,
            items: $this->serializer->serializeItems($result->items)
        );
    }
}

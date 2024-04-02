<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Data;

use App\Modules\Data\Query\GetLanguages\DataGetLanguagesFetcher;
use App\Modules\Data\Query\GetLanguages\DataGetLanguagesQuery;
use App\Modules\Data\Service\LanguageSerializer;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\Translation\Translator;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Response\JsonDataItemsResponse;

#[OA\Get(
    path: '/data/languages',
    description: 'Получение информации об языках',
    summary: 'Получение информации об языках',
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
final readonly class GetLanguagesAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private DataGetLanguagesFetcher $fetcher,
        private Validator $validator,
        private LanguageSerializer $serializer,
        private Translator $translator
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $query = $this->denormalizer->denormalizeQuery($request->getQueryParams(), DataGetLanguagesQuery::class);

        $this->validator->validate($query);

        $result = $this->fetcher->fetch($query, $this->translator->getLocale());

        return new JsonDataItemsResponse(
            count: $result->count,
            items: $this->serializer->serializeItems($result->items)
        );
    }
}

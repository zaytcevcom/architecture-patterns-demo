<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Data;

use App\Modules\Data\Query\GetLanguageByCode\DataGetLanguageByCodeFetcher;
use App\Modules\Data\Service\LanguageSerializer;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\Translation\Translator;
use ZayMedia\Shared\Components\Router\Route;
use ZayMedia\Shared\Http\Exception\NotFoundExceptionModule;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

#[OA\Get(
    path: '/data/languages/{code}',
    description: 'Получение информации об языке по его коду',
    summary: 'Получение информации об языке по его коду',
    tags: ['Data']
)]
#[OA\Parameter(
    name: 'code',
    description: 'Код языка',
    in: 'path',
    required: true,
    schema: new OA\Schema(
        type: 'string'
    ),
    example: 'ru'
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class GetLanguageByCodeAction implements RequestHandlerInterface
{
    public function __construct(
        private DataGetLanguageByCodeFetcher $fetcher,
        private LanguageSerializer $serializer,
        private Translator $translator
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $code = Route::getArgument($request, 'code');

        $result = $this->fetcher->fetch($code, $this->translator->getLocale());

        if (empty($result)) {
            throw new NotFoundExceptionModule(
                module: 'data',
                request: $request,
                message: 'error.language_not_found',
            );
        }

        return new JsonDataResponse(
            data: $this->serializer->serialize($result)
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Audios\Audios;

use App\Modules\Audio\Command\SaveHlsInfo\AudioSaveHlsInfoCommand;
use App\Modules\Audio\Command\SaveHlsInfo\AudioSaveHlsInfoHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Response\JsonDataSuccessResponse;

final readonly class SaveHlsInfoAction implements RequestHandlerInterface
{
    public function __construct(
        private Denormalizer $denormalizer,
        private AudioSaveHlsInfoHandler $handler,
        private Validator $validator
    ) {}

    // todo: обезопасить метод
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $command = $this->denormalizer->denormalize($request->getParsedBody(), AudioSaveHlsInfoCommand::class);

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataSuccessResponse();
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Contacts;

use App\Modules\Contact\Command\ClearSearchHistory\ContactClearSearchHistoryCommand;
use App\Modules\Contact\Command\ClearSearchHistory\ContactClearSearchHistoryHandler;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Components\Validator\Validator;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataSuccessResponse;

#[OA\Delete(
    path: '/users/search/history',
    description: 'Очищает историю поиска по контактам',
    summary: 'Очищает историю поиска по контактам',
    security: [['bearerAuth' => '{}']],
    tags: ['Users (Contacts)']
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class ClearSearchHistoryAction implements RequestHandlerInterface
{
    public function __construct(
        private ContactClearSearchHistoryHandler $handler,
        private Validator $validator
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $command = new ContactClearSearchHistoryCommand(
            userId: $identity->id
        );

        $this->validator->validate($command);

        $this->handler->handle($command);

        return new JsonDataSuccessResponse();
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Action\V1;

use App\Modules\Identity\Query\FindIdByCredentials\Fetcher;
use App\Modules\Identity\Query\FindIdByCredentials\Query;
use App\Modules\OAuth\Entity\User;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use ZayMedia\Shared\Components\Serializer\Denormalizer;
use ZayMedia\Shared\Helpers\OpenApi\ResponseSuccessful;
use ZayMedia\Shared\Http\Response\HtmlResponse;

#[OA\Get(
    path: '/auth',
    description: '**Grant — Authorization Code flow (PKCE)**<br><br>
        1. Необходимо перенаправить пользователя по адресу https://api.lo.ink/v1/auth с передачей данных приложения.<br>
        2. В случае успешной авторизации, пользователь будет **перенаправлен** на указанный в **redirect_uri** адрес с параметром **code** в адресной строке.<br>
        3. По полученному **code** нужно сделать запрос к методу identity/token, где **grant_type** = ```authorization_code```
    ',
    summary: 'OAuth авторизация',
    tags: ['OAuth'],
    parameters: [
        new OA\Parameter(
            name: 'response_type',
            description: 'response_type',
            in: 'query',
            required: true,
            schema: new OA\Schema(
                type: 'string'
            ),
            example: 'code'
        ),
        new OA\Parameter(
            name: 'client_id',
            description: 'client_id',
            in: 'query',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
                format: 'int64'
            ),
            example: 1
        ),
        new OA\Parameter(
            name: 'redirect_uri',
            description: 'redirect_uri',
            in: 'query',
            required: true,
            schema: new OA\Schema(
                type: 'string'
            ),
            example: 'default'
        ),
        new OA\Parameter(
            name: 'code_challenge',
            description: 'code_challenge',
            in: 'query',
            required: true,
            schema: new OA\Schema(
                type: 'string'
            ),
        ),
        new OA\Parameter(
            name: 'code_challenge_method',
            description: 'code_challenge_method',
            in: 'query',
            required: true,
            schema: new OA\Schema(
                type: 'string'
            ),
            example: 'S256'
        ),
        new OA\Parameter(
            name: 'scope',
            description: 'scope',
            in: 'query',
            required: false,
            schema: new OA\Schema(
                type: 'string'
            )
        ),
        new OA\Parameter(
            name: 'state',
            description: 'state',
            in: 'query',
            required: false,
            schema: new OA\Schema(
                type: 'string'
            )
        ),
    ],
    responses: [new ResponseSuccessful()]
)]
final readonly class AuthAction implements RequestHandlerInterface
{
    public function __construct(
        private AuthorizationServer $server,
        private LoggerInterface $logger,
        private Fetcher $users,
        private Environment $template,
        private ResponseFactoryInterface $response,
        private TranslatorInterface $translator,
        private Denormalizer $denormalizer,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $authRequest = $this->server->validateAuthorizationRequest($request);

            if ($request->getMethod() === 'POST') {
                $query = $this->denormalizer->denormalize(
                    data: (array)$request->getParsedBody(),
                    type: Query::class
                );

                $user = $this->users->fetch($query);

                if ($user === null) {
                    $error = $this->translator->trans('error.incorrect_credentials', [], 'oauth');

                    return new HtmlResponse(
                        $this->template->render('authorize.html.twig', compact('query', 'error')),
                        400
                    );
                }

                if (!$user->isActive) {
                    $error = $this->translator->trans('error.not_confirmed', [], 'oauth');

                    return new HtmlResponse(
                        $this->template->render('authorize.html.twig', compact('query', 'error')),
                        409
                    );
                }

                $authRequest->setUser(new User((string)$user->id));
                $authRequest->setAuthorizationApproved(true);

                return $this->server->completeAuthorizationRequest($authRequest, $this->response->createResponse());
            }

            return new HtmlResponse(
                $this->template->render('authorize.html.twig', [])
            );
        } catch (OAuthServerException $exception) {
            $this->logger->warning($exception->getMessage(), ['exception' => $exception]);
            return $exception->generateHttpResponse($this->response->createResponse());
        }
    }
}

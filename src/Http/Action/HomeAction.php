<?php

declare(strict_types=1);

namespace App\Http\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use stdClass;
use ZayMedia\Shared\Components\FeatureToggle\FeatureFlag;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

final readonly class HomeAction implements RequestHandlerInterface
{
    public function __construct(
        private FeatureFlag $flag
    ) {}

    public function handle(Request $request): Response
    {
        if ($this->flag->isEnabled('IS_DEV')) {
            return new JsonDataResponse(['name' => 'API DEVELOP']);
        }

        return new JsonDataResponse(new stdClass());
    }
}

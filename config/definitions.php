<?php

declare(strict_types=1);

use App\Http\Action\V1\Data\SearchAddressAction;

use function DI\autowire;

return [
    SearchAddressAction::class => autowire(),
];

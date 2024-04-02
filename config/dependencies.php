<?php

declare(strict_types=1);

use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;

use function App\Components\env;

$aggregator = new ConfigAggregator(
    providers: [
        new PhpFileProvider(__DIR__ . '/common/*.php'),
        new PhpFileProvider(__DIR__ . '/' . env('APP_ENV', 'prod') . '/*.php'),
    ],
);

return $aggregator->getMergedConfig();

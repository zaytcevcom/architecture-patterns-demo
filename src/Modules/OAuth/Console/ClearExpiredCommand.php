<?php

declare(strict_types=1);

namespace App\Modules\OAuth\Console;

use App\Modules\OAuth\Command\ClearExpiredItems\Command;
use App\Modules\OAuth\Command\ClearExpiredItems\Handler;
use Symfony\Component\Console\Command\Command as ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ClearExpiredCommand extends ConsoleCommand
{
    private Handler $handler;

    public function __construct(Handler $handler)
    {
        parent::__construct();
        $this->handler = $handler;
    }

    protected function configure(): void
    {
        $this
            ->setName('oauth:clear-expired');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->handler->handle(new Command(date(DATE_ATOM)));

        return 0;
    }
}

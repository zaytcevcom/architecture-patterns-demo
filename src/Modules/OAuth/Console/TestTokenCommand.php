<?php

declare(strict_types=1);

namespace App\Modules\OAuth\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Test\Functional\AuthHeader;

final class TestTokenCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('oauth:token')
            ->setDescription('Token command');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        echo AuthHeader::for('8', '-');

        return 0;
    }
}

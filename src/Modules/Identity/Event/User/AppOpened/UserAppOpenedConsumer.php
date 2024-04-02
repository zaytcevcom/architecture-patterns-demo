<?php

declare(strict_types=1);

namespace App\Modules\Identity\Event\User\AppOpened;

use App\Modules\Identity\Event\User\UserHelper;
use App\Modules\Identity\Event\User\UserQueue;
use App\Modules\Notifier\Command\Badge\BadgeCommand;
use App\Modules\Notifier\Command\Badge\BadgeHandler;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ZayMedia\Shared\Components\Queue\Queue;

final class UserAppOpenedConsumer extends Command
{
    public function __construct(
        private readonly Queue $queue,
        private UserHelper $userHelper,
        private readonly BadgeHandler $badgeHandler,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('identity:event-app-opened')
            ->setDescription('Event app opened command');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $callback = function (AMQPMessage $msg): void {
            /**
             * @var array{
             *     userId:int,
             * } $info
             */
            $info = json_decode($msg->getBody(), true);

            $this->badgeHandler->handle(
                new BadgeCommand($info['userId'])
            );
        };

        $this->queue->consume(
            queue: $this->userHelper->getQueueName(UserQueue::OPENED),
            callback: $callback,
        );

        return 0;
    }
}

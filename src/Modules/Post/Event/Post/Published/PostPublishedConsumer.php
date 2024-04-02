<?php

declare(strict_types=1);

namespace App\Modules\Post\Event\Post\Published;

use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ZayMedia\Shared\Components\Queue\Queue;

final class PostPublishedConsumer extends Command
{
    public function __construct(
        private readonly Queue $queue,
        private readonly PostPublishedEvent $postPublishedEvent,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('post:event-post-published')
            ->setDescription('Event post published command');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $callback = function (AMQPMessage $msg): void {
            /**
             * @var array{
             *     userId:int,
             *     postId:int,
             *     socialIds:int[]|null,
             * } $info
             */
            $info = json_decode($msg->getBody(), true);

            $this->postPublishedEvent->handle(
                new PostPublishedData(
                    userId: $info['userId'],
                    postId: $info['postId'],
                    socialIds: $info['socialIds'] ?? []
                )
            );
        };

        $this->queue->consume(
            queue: PostPublishedQueue::getQueueName(),
            callback: $callback,
        );

        return 0;
    }
}

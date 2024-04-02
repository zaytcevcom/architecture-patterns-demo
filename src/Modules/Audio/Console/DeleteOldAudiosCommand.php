<?php

declare(strict_types=1);

namespace App\Modules\Audio\Console;

use App\Modules\Audio\Entity\Audio\Audio;
use App\Modules\Storage\Entity\StorageHostRepository;
use App\Modules\Storage\Service\StorageAudio;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class DeleteOldAudiosCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly StorageHostRepository $storageHostRepository,
        private readonly StorageAudio $storageAudio
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('delete-old-audios')
            ->setDescription('Delete old audios');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln('<info>Delete old audios!</info>');

        $time = time() - 30 * 24 * 60 * 60;

        while (true) {
            /** @var Audio[] $audios */
            $audios = $this->em->getRepository(Audio::class)
                ->matching(
                    Criteria::create()
                        ->andWhere(new Comparison('deletedAt', Comparison::LT, $time))
                        ->andWhere(Criteria::expr()->neq('deletedAt', null))
                        ->andWhere(Criteria::expr()->isNull('sourceDeletedAt'))
                        ->orderBy(['createdAt' => 'ASC'])
                        ->setMaxResults(50)
                );

            if (\count($audios) === 0) {
                $output->writeln('<info>Sleep!</info>');
                sleep(60);
            }

            $deletedAt = time();

            foreach ($audios as $audio) {
                $host = $audio->getSourceHost()?->getValue();
                $fileId = $audio->getSourceFileId()?->getValue();

                if ($host !== null && $fileId !== null) {
                    $storageHost = $this->storageHostRepository->getByHost($host);

                    $this->storageAudio->setHost($host);
                    $this->storageAudio->setApiKey($storageHost->getSecret());

                    $this->storageAudio->markDelete($fileId);
                }

                $audio->setSourceDeletedAt($deletedAt);

                $this->em->persist($audio);
                $this->em->flush();

                $output->writeln('<info>audio ' . $audio->getId() . ' deleted!</info>');
            }

            $this->em->clear();
        }
    }
}

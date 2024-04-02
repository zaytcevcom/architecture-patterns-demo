<?php

declare(strict_types=1);

namespace App\Modules\Identity\Console\Typesense;

use App\Modules\Identity\Entity\User\User;
use App\Modules\Identity\Service\Typesense\User\UserCollection;
use App\Modules\Identity\Service\Typesense\User\UserDocument;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

final class UserRefreshCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserCollection $userCollection,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('typesense:user:refresh')
            ->setDescription('Create user collection in Typesense');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->recreateSchema();

        $maxId = null;
        $count = 0;

        try {
            while (true) {
                ++$count;

                $users = $this->getUsers($maxId);

                if (\count($users) === 0) {
                    return 0;
                }

                $documents = [];

                foreach ($users as $user) {
                    $maxId = $user->getId();

                    $title = trim($user->getFirstName()->getValue() . ' ' . $user->getLastName()->getValue());

                    $documents[] = new UserDocument(
                        identifier: $user->getId(),
                        title: $title,
                        countryId: $user->getCountry()?->getId(),
                        cityId: $user->getCity()?->getId(),
                        marital: $user->getMarital()?->getValue(),
                        sex: $user->getSex()?->getValue(),
                        birthdayYear: $user->getYear()
                    );
                }

                $this->userCollection->upsertDocuments($documents);

                $output->writeln('Count: ' . $count);
            }
        } catch (Throwable $throwable) {
            $output->writeln($throwable->getMessage());
        }

        return 0;
    }

    private function recreateSchema(): void
    {
        try {
            $this->userCollection->deleteSchema();
        } catch (Throwable) {
        }

        try {
            $this->userCollection->createSchema();
        } catch (Throwable) {
        }
    }

    /** @return User[] */
    private function getUsers(?int $maxId): array
    {
        $this->em->clear();

        $criteria = Criteria::create();

        if (null !== $maxId) {
            $criteria->andWhere(Criteria::expr()->lt('id', $maxId));
        }

        /** @var User[] $users */
        $users = $this->em->getRepository(User::class)
            ->matching(
                $criteria
                    ->orderBy(['id' => 'DESC'])
                    ->setMaxResults(5_000)
            );

        /** @var User[] $result */
        $result = [];

        foreach ($users as $user) {
            $result[] = $user;
        }

        return $result;
    }
}

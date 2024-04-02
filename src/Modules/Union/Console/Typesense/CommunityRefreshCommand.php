<?php

declare(strict_types=1);

namespace App\Modules\Union\Console\Typesense;

use App\Modules\Union\Entity\Union\Union;
use App\Modules\Union\Query\UnionCategory\Community\All\UnionCategoryCommunityFetcher;
use App\Modules\Union\Query\UnionCategory\Community\All\UnionCategoryCommunityQuery;
use App\Modules\Union\Query\UnionSphere\GetByCategoryIds\UnionSphereGetByCategoryIdsFetcher;
use App\Modules\Union\Query\UnionSphere\GetByCategoryIds\UnionSphereGetByCategoryIdsQuery;
use App\Modules\Union\Service\Typesense\Community\CommunityCollection;
use App\Modules\Union\Service\Typesense\Community\CommunityDocument;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

final class CommunityRefreshCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CommunityCollection $communityCollection,
        private readonly UnionSphereGetByCategoryIdsFetcher $sphereFetcher,
        private readonly UnionCategoryCommunityFetcher $categoryCommunityFetcher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('typesense:community:refresh')
            ->setDescription('Create community collection in Typesense');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->recreateSchema();

        $maxId = null;
        $count = 0;

        try {
            $kinds = $this->getKinds();

            while (true) {
                ++$count;

                $unions = $this->getCommunities($maxId);

                if (\count($unions) === 0) {
                    return 0;
                }

                $documents = [];

                $categoryIds = [];

                foreach ($unions as $union) {
                    if ($categoryId = $union->getCategoryId()) {
                        $categoryIds[] = $categoryId;
                    }
                }

                $spheres = $this->getSpheres($categoryIds);

                foreach ($unions as $union) {
                    $maxId = $union->getId();

                    $sphereId = ($categoryId = $union->getCategoryId()) ? $spheres[$categoryId] ?? null : null;
                    $categoryKind = ($categoryId = $union->getCategoryId()) ? $kinds[$categoryId] ?? null : null;

                    $documents[] = new CommunityDocument(
                        identifier: $union->getId(),
                        title: $union->getName(),
                        sphereId: $sphereId,
                        categoryId: $union->getCategoryId(),
                        categoryKind: $categoryKind,
                        cityId: $union->getCityId(),
                        countMembers: $union->getCountMembers()
                    );
                }

                $this->communityCollection->upsertDocuments($documents);

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
            $this->communityCollection->deleteSchema();
        } catch (Throwable) {
        }

        try {
            $this->communityCollection->createSchema();
        } catch (Throwable) {
        }
    }

    /** @return Union[] */
    private function getCommunities(?int $maxId): array
    {
        $this->em->clear();

        $criteria = Criteria::create();

        if (null !== $maxId) {
            $criteria->andWhere(Criteria::expr()->lt('id', $maxId));
        }

        /** @var Union[] $unions */
        $unions = $this->em->getRepository(Union::class)
            ->matching(
                $criteria
                    ->andWhere(Criteria::expr()->eq('type', Union::typeCommunity()))
                    ->orderBy(['id' => 'DESC'])
                    ->setMaxResults(5_000)
            );

        /** @var Union[] $result */
        $result = [];

        foreach ($unions as $union) {
            $result[] = $union;
        }

        return $result;
    }

    /** @return array<int, int> */
    private function getSpheres(array $ids): array
    {
        $this->em->clear();

        $ids = array_unique($ids);

        $result = [];
        $chunks = array_chunk($ids, 1000);

        foreach ($chunks as $chunk) {
            /** @var array{id: int, category_id: int}[] $spheres */
            $spheres = $this->sphereFetcher->fetch(
                new UnionSphereGetByCategoryIdsQuery($chunk, 'en')
            );

            foreach ($spheres as $sphere) {
                if (isset($result[$sphere['category_id']])) {
                    continue;
                }

                $result[$sphere['category_id']] = $sphere['id'];
            }
        }

        return $result;
    }

    /**
     * @return array<int, int>
     * @throws Exception
     */
    private function getKinds(): array
    {
        $result = [];

        /** @var array{id: int, kind: int}[] $categories */
        $categories = $this->categoryCommunityFetcher->fetch(
            new UnionCategoryCommunityQuery(search: null, filter: null, count: 5000)
        )->items;

        foreach ($categories as $category) {
            if (isset($result[$category['id']])) {
                continue;
            }

            $result[$category['id']] = $category['kind'];
        }

        return $result;
    }
}

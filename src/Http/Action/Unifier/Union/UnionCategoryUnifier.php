<?php

declare(strict_types=1);

namespace App\Http\Action\Unifier\Union;

use App\Modules\Union\Query\UnionSphere\GetByCategoryIds\UnionSphereGetByCategoryIdsFetcher;
use App\Modules\Union\Query\UnionSphere\GetByCategoryIds\UnionSphereGetByCategoryIdsQuery;
use App\Modules\Union\Service\UnionCategorySerializer;
use Symfony\Component\Translation\Translator;
use ZayMedia\Shared\Http\Unifier\UnifierInterface;

final readonly class UnionCategoryUnifier implements UnifierInterface
{
    public function __construct(
        private UnionSphereGetByCategoryIdsFetcher $sphereFetcher,
        private UnionCategorySerializer $categorySerializer,
        private Translator $translator
    ) {}

    public function unifyOne(?int $userId, ?array $item): array
    {
        /** @var array{array} $result */
        $result = $this->unify($userId, (null !== $item) ? [$item] : []);
        return (isset($result[0])) ? $result[0] : [];
    }

    public function unify(?int $userId, array $items): array
    {
        $items = $this->categorySerializer->serializeItems($items);

        $entityIds = $this->getEntityIds($items);

        return $this->mapSpheres($items, $this->getSpheres($entityIds['categoryIds']));
    }

    private function getSpheres(array $ids): array
    {
        return $this->sphereFetcher->fetch(
            new UnionSphereGetByCategoryIdsQuery(
                ids: $ids,
                locale: $this->translator->getLocale()
            )
        );
    }

    private function mapSpheres(array $items, array $spheres): array
    {
        /**
         * @var int $key
         * @var array{array{id:int|null}} $items
         */
        foreach ($items as $key => $item) {
            $items[$key]['sphere'] = null;

            /** @var array{category_id:int} $sphere */
            foreach ($spheres as $sphere) {
                if ($item['id'] === $sphere['category_id']) {
                    $items[$key]['sphere'] = $this->categorySerializer->serialize(
                        $sphere
                    );
                    break;
                }
            }
        }

        return $items;
    }

    /** @return array{categoryIds:int[]} */
    private function getEntityIds(array $items): array
    {
        $categoryIds    = [];

        /** @var array{id:int} $item */
        foreach ($items as $item) {
            $categoryIds[] = $item['id'];
        }

        return [
            'categoryIds' => array_unique($categoryIds),
        ];
    }
}

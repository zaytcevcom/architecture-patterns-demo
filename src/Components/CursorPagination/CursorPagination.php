<?php

declare(strict_types=1);

namespace App\Components\CursorPagination;

use Doctrine\DBAL\Query\QueryBuilder;
use Exception;

final class CursorPagination
{
    private const SALT = 'zAS0p5NUHYuGhzID7PIT';

    /** @param array<string, string> $orderingBy */
    public static function generateResult(
        QueryBuilder $query,
        ?string $cursor,
        int $count,
        bool $isSortDescending,
        array $orderingBy,
        string $field,
        ?int $limitValue,
        ?int $offset = null
    ): CursorPaginationResult {
        $totalCount = (null === $cursor) ? self::totalCount(clone $query, $field) : null;

        if (null !== $offset) {
            $query->setFirstResult($offset);

            foreach ($orderingBy as $sort => $order) {
                $query->addOrderBy($sort, $order);
            }
        } else {
            $cursorData = self::decode($cursor);

            if (null !== $cursorData) {
                if (isset($cursorData[$field])) {
                    $id = (int)$cursorData[$field];
                    $directly = (bool)$cursorData['directly'];

                    if ($directly) {
                        if ($isSortDescending) {
                            $query->andWhere($field . ' < ' . $id);
                        } else {
                            $query->andWhere($field . ' > ' . $id);
                        }

                        foreach ($orderingBy as $sort => $order) {
                            $query->addOrderBy($sort, $order);
                        }
                    } else {
                        if ($isSortDescending) {
                            $query->andWhere($field . ' >= ' . $id);
                        } else {
                            $query->andWhere($field . ' <= ' . $id);
                        }

                        foreach ($orderingBy as $sort => $order) {
                            $query->addOrderBy($sort, $order === 'DESC' ? 'ASC' : 'DESC');
                        }
                    }
                }
            } else {
                foreach ($orderingBy as $sort => $order) {
                    $query->addOrderBy($sort, $order);
                }
            }
        }

        try {
            $rows = $query
                ->setMaxResults($count + 1)
                ->executeQuery()
                ->fetchAllAssociative();
        } catch (Exception) {
            $rows = [];
        }

        $items = [];

        foreach ($rows as $key => $row) {
            if (isset($directly) && !$directly && $key === 0) {
                continue;
            }

            if (\count($items) >= $count) {
                break;
            }

            $items[] = $row;
        }

        if (isset($directly) && !$directly) {
            $items = array_reverse($items);
        }

        $cursorPrev = null;

        if (isset($id)) {
            $cursorPrev = self::getPrevCursor($items, $limitValue, $isSortDescending, $field);
        }

        $cursorNext = (\count($rows) > $count) ? self::getNextCursor($items, $isSortDescending, $field) : null;

        return new CursorPaginationResult(
            count: $totalCount,
            items: $items,
            cursor: new Cursor(
                prev: $cursorPrev,
                next: $cursorNext
            )
        );
    }

    public static function getPrevCursor(array $items, ?int $limitValue, bool $isSortDescending, string $field): ?string
    {
        if (null === $limitValue) {
            return null;
        }

        $id = null;

        $fieldAfterDot = self::getAfterDot($field);

        /** @var array|float|int|string $item */
        foreach ($items as $item) {
            if (!isset($item[$fieldAfterDot]) || !is_numeric($item[$fieldAfterDot])) {
                continue;
            }

            $value = (int)$item[$fieldAfterDot];

            if (null === $id) {
                $id = $value;
            }

            if ($isSortDescending) {
                if ($value > $id) {
                    $id = $value;
                }
            } else {
                if ($value < $id) {
                    $id = $value;
                }
            }
        }

        $id = ($limitValue !== $id) ? $id : null;

        return self::encode($id, $field, false);
    }

    public static function getNextCursor(array $items, bool $isSortDescending, string $field): ?string
    {
        $id = null;

        $fieldAfterDot = self::getAfterDot($field);

        /** @var array|float|int|string $item */
        foreach ($items as $item) {
            if (!isset($item[$fieldAfterDot]) || !is_numeric($item[$fieldAfterDot])) {
                continue;
            }

            $value = (int)$item[$fieldAfterDot];

            if (null === $id) {
                $id = $value;
            }

            if ($isSortDescending) {
                if ($value < $id) {
                    $id = $value;
                }
            } else {
                if ($value > $id) {
                    $id = $value;
                }
            }
        }

        return self::encode($id, $field, true);
    }

    public static function encode(?int $value, string $field, bool $directly): ?string
    {
        if (null === $value) {
            return null;
        }

        $value = json_encode([
            $field => $value,
            'directly' => $directly,
        ]);

        try {
            return base64_encode(base64_encode($value) . self::SALT);
        } catch (Exception) {
            return null;
        }
    }

    public static function decode(?string $value): ?array
    {
        if (null === $value) {
            return null;
        }

        try {
            $value = substr(
                string: base64_decode($value, true),
                offset: 0,
                length: -1 * \strlen(self::SALT)
            );

            $value = base64_decode($value, true);

            return (array)json_decode($value, true);
        } catch (Exception) {
            return null;
        }
    }

    private static function totalCount(QueryBuilder $query, string $field): ?int
    {
        try {
            $result = $query
                ->select('COUNT(DISTINCT ' . $field . ') AS count')
                ->setFirstResult(0)
                ->fetchAssociative();

            return (int)($result['count'] ?? 0);
        } catch (Exception) {
        }

        return null;
    }

    private static function getAfterDot(string $string): string
    {
        $parts = explode('.', $string);
        return end($parts);
    }
}

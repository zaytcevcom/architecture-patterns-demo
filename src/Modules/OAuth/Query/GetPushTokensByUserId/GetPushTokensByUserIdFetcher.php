<?php

declare(strict_types=1);

namespace App\Modules\OAuth\Query\GetPushTokensByUserId;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Exception;

final readonly class GetPushTokensByUserIdFetcher
{
    public function __construct(
        private Connection $connection
    ) {}

    /**
     * @return array{bundleId: string, platform: string, token: string, voip_token: string, locale: string}[]
     * @throws Exception
     */
    public function fetch(int $userId): array
    {
        $date = new DateTimeImmutable('+5 minutes');

        $result = $this->connection->createQueryBuilder()
            ->select(['bundle_id', 'push_token', 'voip_token', 'system_name', 'locale'])
            ->from('oauth_refresh_tokens')
            ->andWhere('user_identifier = :userId')
            ->andWhere('push_token IS NOT NULL')
            ->andWhere('push_token != ""')
            ->andWhere('voip_token IS NOT NULL')
            ->andWhere('voip_token != ""')
            ->andWhere('expiry_date_time > :date')
            ->orderBy('expiry_date_time', 'DESC')
            ->setMaxResults(100)
            ->setParameter('userId', $userId)
            ->setParameter('date', $date->format(DATE_ATOM))
            ->distinct()
            ->executeQuery();

        /** @var array{bundle_id: string, push_token: string, voip_token: string, system_name: string, locale: string}[] $rows */
        $rows = $result->fetchAllAssociative();

        $items = [];

        foreach ($rows as $row) {
            $platform = $this->getPlatform($row['system_name']);

            if (empty($platform)) {
                continue;
            }

            if (\in_array($row['push_token'], array_column($items, 'token'), true)) {
                continue;
            }

            $items[] = [
                'bundleId'      => $row['bundle_id'],
                'platform'      => $platform,
                'token'         => $row['push_token'],
                'voip_token'    => $row['voip_token'],
                'locale'        => $row['locale'],
            ];
        }

        /** @var array{bundleId: string, platform: string, token: string, voip_token: string, locale: string}[] $items */
        return $items;
    }

    private function getPlatform(?string $value = null): ?string
    {
        if (empty($value)) {
            return null;
        }

        $value = strtolower(trim($value));

        if (\in_array($value, ['ios', 'iphoneos', 'ipados', 'ipads'], true)) {
            return 'ios';
        }

        return 'android';
    }
}

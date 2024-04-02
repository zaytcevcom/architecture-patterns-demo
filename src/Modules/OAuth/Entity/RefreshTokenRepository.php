<?php

declare(strict_types=1);

namespace App\Modules\OAuth\Entity;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

final class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    /** @var EntityRepository<RefreshToken> */
    private EntityRepository $repo;
    private EntityManagerInterface $em;

    /**
     * @param EntityRepository<RefreshToken> $repo
     */
    public function __construct(EntityManagerInterface $em, EntityRepository $repo)
    {
        $this->repo = $repo;
        $this->em = $em;
    }

    public function getNewRefreshToken(): ?RefreshToken
    {
        $refreshToken = new RefreshToken();
        $refreshToken->setCreatedAt(time());

        return $refreshToken;
    }

    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity): void
    {
        if ($this->exists($refreshTokenEntity->getIdentifier())) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }

        $this->em->persist($refreshTokenEntity);
        $this->em->flush();
    }

    public function revokeRefreshToken($tokenId): void
    {
        if ($token = $this->repo->find($tokenId)) {
            $date = new DateTimeImmutable('+5 minutes');

            if ($pushToken = $token->getPushToken()) {
                $this->em->createQueryBuilder()
                    ->update(RefreshToken::class, 'rt')
                    ->set('rt.expiryDateTime', ':expiryDateTime')
                    ->andWhere('rt.pushToken = :pushToken')
                    ->andWhere('rt.pushToken IS NOT NULL')
                    ->andWhere('rt.createdAt < :date')
                    ->setParameter('expiryDateTime', $date->format(DATE_ATOM))
                    ->setParameter('pushToken', $pushToken)
                    ->setParameter('date', time())
                    ->getQuery()
                    ->execute();
            } else {
                $token->setExpiryDateTime($date);
                $this->em->flush();
            }
        }
    }

    public function revokeRefreshTokenAndAllSamePushTokens(string $tokenId): void
    {
        if ($token = $this->repo->find($tokenId)) {
            $this->em->remove($token);
            $this->em->flush();

            if ($pushToken = $token->getPushToken()) {
                $this->removeAllForPushToken($pushToken);
            }
        }
    }

    public function isRefreshTokenRevoked($tokenId): bool
    {
        return !$this->exists($tokenId);
    }

    public function removeAllForUser(string $userId): void
    {
        $this->em->createQueryBuilder()
            ->delete(RefreshToken::class, 'rt')
            ->andWhere('rt.userIdentifier = :user_id')
            ->setParameter(':user_id', $userId)
            ->getQuery()
            ->execute();
    }

    public function removeAllForPushToken(string $pushToken): void
    {
        $query = $this->em->createQueryBuilder()
            ->delete(RefreshToken::class, 'rt')
            ->andWhere('rt.pushToken = :pushToken')
            ->andWhere('rt.pushToken IS NOT NULL');

        $query
            ->setParameter(':pushToken', $pushToken)
            ->getQuery()
            ->execute();
    }

    public function findByIdentifier(string $identifier): ?RefreshToken
    {
        return $this->repo->findOneBy(['identifier' => $identifier]);
    }

    public function removeAllExpired(DateTimeImmutable $now): void
    {
        $this->em->createQueryBuilder()
            ->delete(RefreshToken::class, 'rt')
            ->andWhere('rt.expiryDateTime < :date')
            ->setParameter(':date', $now->format(DATE_ATOM))
            ->getQuery()
            ->execute();
    }

    private function exists(string $id): bool
    {
        try {
            return $this->repo->createQueryBuilder('t')
                ->select('COUNT(t.identifier)')
                ->andWhere('t.identifier = :identifier')
                ->andWhere('t.expiryDateTime > :date')
                ->setParameter(':identifier', $id)
                ->setParameter(':date', new DateTimeImmutable())
                ->getQuery()
                ->getSingleScalarResult() > 0;
        } catch (Exception) {
            return false;
        }
    }
}

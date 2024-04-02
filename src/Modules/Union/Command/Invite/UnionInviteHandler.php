<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Invite;

use App\Modules\Contact\Query\IsContact\ContactIsContactFetcher;
use App\Modules\Contact\Query\IsContact\ContactIsContactQuery;
use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Identity\Service\IdentityRealtimeNotifier;
use App\Modules\Union\Entity\Union\Union;
use App\Modules\Union\Entity\Union\UnionRepository;
use App\Modules\Union\Entity\UnionUser\UnionUser;
use App\Modules\Union\Entity\UnionUser\UnionUserRepository;
use App\Modules\Union\Query\Community\GetRequestsCount\UnionGetRequestsCountFetcher;
use Doctrine\DBAL\Exception;
use ZayMedia\Shared\Components\Flusher;
use ZayMedia\Shared\Http\Exception\AccessDeniedException;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

final readonly class UnionInviteHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private UnionRepository $unionRepository,
        private UnionUserRepository $unionUserRepository,
        private ContactIsContactFetcher $isContactFetcher,
        private UnionGetRequestsCountFetcher $unionGetRequestsCountFetcher,
        private IdentityRealtimeNotifier $identityRealtimeNotifier,
        private Flusher $flusher
    ) {}

    /**
     * @throws Exception
     */
    public function handle(UnionInviteCommand $command): void
    {
        $userSource   = $this->userRepository->getById($command->sourceId);
        $userTarget   = $this->userRepository->getById($command->targetId);
        $union        = $this->unionRepository->getById($command->unionId);

        if ($union->getKind() !== Union::kindPublic()) {
            throw new AccessDeniedException();
        }

        // Check is contact
        $queryIsContact = new ContactIsContactQuery(
            userId: $userSource->getId(),
            contactId: $userTarget->getId()
        );

        if ($this->isContactFetcher->fetch($queryIsContact)) {
            throw new AccessDeniedException();
        }

        // Check max limit
        if ($this->unionUserRepository->countInviteByUserId($userSource->getId()) >= UnionUser::limitInviteTotal()) {
            throw new DomainExceptionModule(
                module: 'union',
                message: 'error.union_user.limit_invite_total',
                code: 3
            );
        }

        // Check daily limit
        if ($this->unionUserRepository->countInviteTodayByUserId($userSource->getId()) >= UnionUser::limitInviteDaily()) {
            throw new DomainExceptionModule(
                module: 'union',
                message: 'error.union_user.limit_invite_daily',
                code: 4
            );
        }

        $unionUser = $this->unionUserRepository->findByUserAndUnionIds(
            userId: $userTarget->getId(),
            unionId: $union->getId()
        );

        if (!empty($unionUser)) {
            return;
        }

        $unionUser = UnionUser::invite(
            sourceId: $userSource->getId(),
            targetId: $userTarget->getId(),
            unionId: $union->getId()
        );

        $this->unionUserRepository->add($unionUser);

        $this->flusher->flush();

        $this->updateCounterCommunities($userTarget->getId());
    }

    /**
     * @throws Exception
     */
    private function updateCounterCommunities(int $userId): void
    {
        $this->identityRealtimeNotifier->updateCounterCommunities(
            userId: $userId,
            count: $this->unionGetRequestsCountFetcher->fetch($userId)
        );
    }
}

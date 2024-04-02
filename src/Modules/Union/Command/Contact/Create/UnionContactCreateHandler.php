<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Contact\Create;

use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Post\Entity\Post\Post;
use App\Modules\Union\Command\UpdateCounter\UnionUpdateCounterContactsHandler;
use App\Modules\Union\Entity\Union\Union;
use App\Modules\Union\Entity\Union\UnionRepository;
use App\Modules\Union\Entity\UnionContact\UnionContact;
use App\Modules\Union\Entity\UnionContact\UnionContactRepository;
use ZayMedia\Shared\Components\Flusher;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

final readonly class UnionContactCreateHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private UnionRepository $unionRepository,
        private UnionContactRepository $unionContactRepository,
        private UnionUpdateCounterContactsHandler $unionUpdateCounterContactsHandler,
        private Flusher $flusher
    ) {}

    public function handle(UnionContactCreateCommand $command): UnionContact
    {
        $user = $this->userRepository->getById($command->userId);
        $union = $this->unionRepository->getById($command->unionId);

        $this->checkLimitUnion($union);

        $contact = UnionContact::create(
            unionId: $union->getId(),
            userId: $user->getId(),
            position: $command->position,
            email: $command->email,
            phone: $command->phone,
        );

        $this->unionContactRepository->add($contact);

        $this->flusher->flush();

        $this->unionUpdateCounterContactsHandler->handle($union->getId());

        return $contact;
    }

    private function checkLimitUnion(Union $union): void
    {
        // Check max limit
        if ($this->unionContactRepository->countByUnionId($union->getId()) >= Post::limitTotal()) {
            throw new DomainExceptionModule(
                module: 'post',
                message: 'error.union_contact.limit_total',
                code: 2
            );
        }
    }
}

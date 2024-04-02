<?php

declare(strict_types=1);

namespace App\Modules\Union\Command\Link\Create;

use App\Modules\Post\Entity\Post\Post;
use App\Modules\Union\Command\UpdateCounter\UnionUpdateCounterLinksHandler;
use App\Modules\Union\Entity\Union\Union;
use App\Modules\Union\Entity\Union\UnionRepository;
use App\Modules\Union\Entity\UnionLink\UnionLink;
use App\Modules\Union\Entity\UnionLink\UnionLinkRepository;
use ZayMedia\Shared\Components\Flusher;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

final readonly class UnionLinkCreateHandler
{
    public function __construct(
        private UnionRepository $unionRepository,
        private UnionLinkRepository $unionLinkRepository,
        private UnionUpdateCounterLinksHandler $unionUpdateCounterLinksHandler,
        private Flusher $flusher
    ) {}

    public function handle(UnionLinkCreateCommand $command): UnionLink
    {
        $union = $this->unionRepository->getById($command->unionId);

        $this->checkLimitUnion($union);

        $link = UnionLink::create(
            unionId: $union->getId(),
            url: $command->url,
            title: $command->title,
        );

        $this->unionLinkRepository->add($link);

        $this->flusher->flush();

        $this->unionUpdateCounterLinksHandler->handle($union->getId());

        return $link;
    }

    private function checkLimitUnion(Union $union): void
    {
        // Check max limit
        if ($this->unionLinkRepository->countByUnionId($union->getId()) >= Post::limitTotal()) {
            throw new DomainExceptionModule(
                module: 'post',
                message: 'error.union_link.limit_total',
                code: 2
            );
        }
    }
}

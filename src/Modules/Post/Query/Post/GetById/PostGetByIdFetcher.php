<?php

declare(strict_types=1);

namespace App\Modules\Post\Query\Post\GetById;

use App\Modules\Post\Query\Post\GetByIds\PostGetByIdsFetcher;
use App\Modules\Post\Query\Post\GetByIds\PostGetByIdsQuery;
use ZayMedia\Shared\Http\Exception\DomainExceptionModule;

final readonly class PostGetByIdFetcher
{
    public function __construct(
        private PostGetByIdsFetcher $postGetByIdsFetcher
    ) {}

    public function fetch(PostGetByIdQuery $query): array
    {
        $result = $this->postGetByIdsFetcher->fetch(
            new PostGetByIdsQuery([$query->id])
        );

        if (empty($result)) {
            throw new DomainExceptionModule(
                module: 'post',
                message: 'error.post.post_not_found',
                code: 1
            );
        }

        return (array)$result[0];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Action\Unifier\Post;

use App\Modules\Audio\Query\Audio\GetByIds\AudioGetByIdsFetcher;
use App\Modules\Audio\Query\Audio\GetByIds\AudioGetByIdsQuery;
use App\Modules\Audio\Query\AudioAlbum\GetByIds\AudioAlbumGetByIdsFetcher;
use App\Modules\Audio\Query\AudioAlbum\GetByIds\AudioAlbumGetByIdsQuery;
use App\Modules\Audio\Service\AudioAlbumSerializer;
use App\Modules\Audio\Service\AudioSerializer;
use App\Modules\Flow\Query\Flow\GetByIds\FlowGetByIdsFetcher;
use App\Modules\Flow\Query\Flow\GetByIds\FlowGetByIdsQuery;
use App\Modules\Flow\Service\FlowSerializer;
use App\Modules\Identity\Query\GetByIds\IdentityGetByIdsFetcherCached;
use App\Modules\Identity\Query\GetByIds\IdentityGetByIdsQuery;
use App\Modules\Identity\Service\UserSerializer;
use App\Modules\Media\Query\Video\GetByIds\VideoGetByIdsFetcher;
use App\Modules\Media\Query\Video\GetByIds\VideoGetByIdsQuery;
use App\Modules\Media\Service\VideoSerializer;
use App\Modules\Photo\Query\Photo\GetByIds\PhotoGetByIdsFetcher;
use App\Modules\Photo\Query\Photo\GetByIds\PhotoGetByIdsQuery;
use App\Modules\Photo\Service\PhotoSerializer;
use App\Modules\Post\Query\Post\GetByIds\PostGetByIdsFetcher;
use App\Modules\Post\Query\Post\GetByIds\PostGetByIdsQuery;
use App\Modules\Post\Query\Post\GetRepostedPostIds\PostGetRepostedPostIdsFetcher;
use App\Modules\Post\Query\Post\GetRepostedPostIds\PostGetRepostedPostIdsQuery;
use App\Modules\Post\Query\PostComment\GetCommentedPostIds\PostGetCommentedPostIdsFetcher;
use App\Modules\Post\Query\PostComment\GetCommentedPostIds\PostGetCommentedPostIdsQuery;
use App\Modules\Post\Query\PostLike\GetLikedPostIds\PostLikeGetLikedPostIdsFetcher;
use App\Modules\Post\Query\PostLike\GetLikedPostIds\PostLikeGetLikedPostIdsQuery;
use App\Modules\Post\Service\PostSerializer;
use App\Modules\Union\Query\Union\GetByIds\UnionGetByIdsFetcherCached;
use App\Modules\Union\Query\Union\GetByIds\UnionGetByIdsQuery;
use App\Modules\Union\Service\UnionSerializer;
use ZayMedia\Shared\Http\Unifier\UnifierInterface;

final readonly class PostUnifier implements UnifierInterface
{
    public function __construct(
        private PostSerializer $postSerializer,
        private FlowSerializer $flowSerializer,
        private IdentityGetByIdsFetcherCached $userFetcher,
        private UserSerializer $userSerializer,
        private UnionGetByIdsFetcherCached $unionGetByIdsFetcher,
        private UnionSerializer $unionSerializer,
        private PhotoGetByIdsFetcher $photoGetByIdsFetcher,
        private PhotoSerializer $photoSerializer,
        private AudioGetByIdsFetcher $audioGetByIdsFetcher,
        private AudioSerializer $audioSerializer,
        private AudioAlbumGetByIdsFetcher $audioAlbumGetByIdsFetcher,
        private AudioAlbumSerializer $audioAlbumSerializer,
        private VideoGetByIdsFetcher $videoGetByIdsFetcher,
        private VideoSerializer $videoSerializer,
        private PostGetByIdsFetcher $postGetByIdsFetcher,
        private PostLikeGetLikedPostIdsFetcher $postLikeGetLikedPostIdsFetcher,
        private PostGetCommentedPostIdsFetcher $postGetCommentedPostIdsFetcher,
        private PostGetRepostedPostIdsFetcher $postGetRepostedPostIdsFetcher,
        private FlowGetByIdsFetcher $flowGetByIdsFetcher,
    ) {}

    public function unifyOne(?int $userId, ?array $item): array
    {
        /** @var array{array} $result */
        $result = $this->unify($userId, (null !== $item) ? [$item] : []);
        return (isset($result[0])) ? $result[0] : [];
    }

    public function unify(?int $userId, array $items): array
    {
        $items = $this->postSerializer->serializeItems($items);

        if (null !== $userId) {
            $items = $this->mapPermissions($userId, $items);
        }

        $entityIds = $this->getEntityIds($items);

        $posts = $this->getPosts($entityIds['repostIds']);
        $postEntityIds = $this->getEntityIds($posts);

        $flows = $this->getFlows([...$entityIds['flowIds'], ...$postEntityIds['flowIds']]);
        $flowEntityIds = $this->getFlowEntityIds($flows);

        $users  = $this->getUsers([...$entityIds['userIds'], ...$postEntityIds['userIds'], ...$flowEntityIds['userIds']]);
        $unions = $this->getUnions([...$entityIds['unionIds'], ...$postEntityIds['unionIds'], ...$flowEntityIds['userIds']]);
        $photos = $this->getPhotos([...$entityIds['photoIds'], ...$postEntityIds['photoIds']]);
        $audios = $this->getAudios([...$entityIds['audioIds'], ...$postEntityIds['audioIds']]);
        $videos = $this->getVideos([...$entityIds['videoIds'], ...$postEntityIds['videoIds']]);

        $flows = $this->mapUsers($flows, $users);
        $flows = $this->mapUnions($flows, $unions);

        $items = $this->mapUsers($items, $users);
        $items = $this->mapUnions($items, $unions);
        $items = $this->mapPhotos($items, $photos);
        $items = $this->mapAudios($items, $audios);
        $items = $this->mapVideos($items, $videos);
        $items = $this->mapFlows($items, $flows);

        $items = $this->mapPosts($items, $posts, $users, $unions, $photos, $audios, $videos, $flows);

        if (null !== $userId) {
            $items = $this->mapLiked($items, $this->getLiked($userId, $entityIds['postIds']));
            $items = $this->mapCommented($items, $this->getCommented($userId, $entityIds['postIds']));
            $items = $this->mapReposted($items, $this->getReposted($userId, $entityIds['postIds']));
        }

        return $items;
    }

    private function getUsers(array $ids): array
    {
        return $this->userSerializer->serializeItems(
            $this->userFetcher->fetch(
                new IdentityGetByIdsQuery($ids)
            )
        );
    }

    private function getUnions(array $ids): array
    {
        return $this->unionSerializer->serializeItems(
            $this->unionGetByIdsFetcher->fetch(
                new UnionGetByIdsQuery($ids)
            )
        );
    }

    private function getPhotos(array $ids): array
    {
        return $this->photoSerializer->serializeItems(
            $this->photoGetByIdsFetcher->fetch(
                new PhotoGetByIdsQuery($ids)
            )
        );
    }

    private function getAudios(array $ids): array
    {
        $items = $this->audioSerializer->serializeItems(
            $this->audioGetByIdsFetcher->fetch(
                new AudioGetByIdsQuery($ids)
            )
        );

        $albumIds = [];

        /** @var array{albumId:int} $item */
        foreach ($items as $item) {
            if (!empty($item['albumId'])) {
                $albumIds[] = $item['albumId'];
            }
        }

        return $this->mapAudioAlbums($items, $this->getAudioAlbums($albumIds));
    }

    private function getAudioAlbums(array $ids): array
    {
        return $this->audioAlbumSerializer->serializeItems(
            $this->audioAlbumGetByIdsFetcher->fetch(
                new AudioAlbumGetByIdsQuery($ids)
            )
        );
    }

    private function getVideos(array $ids): array
    {
        return $this->videoSerializer->serializeItems(
            $this->videoGetByIdsFetcher->fetch(
                new VideoGetByIdsQuery($ids)
            )
        );
    }

    private function getPosts(array $ids): array
    {
        return $this->postSerializer->serializeItems(
            $this->postGetByIdsFetcher->fetch(
                new PostGetByIdsQuery($ids)
            )
        );
    }

    private function getFlows(array $ids): array
    {
        return $this->flowSerializer->serializeItems(
            $this->flowGetByIdsFetcher->fetch(
                new FlowGetByIdsQuery($ids)
            )
        );
    }

    private function getLiked(?int $userId, array $ids): array
    {
        if (empty($userId)) {
            return [];
        }

        return $this->postLikeGetLikedPostIdsFetcher->fetch(
            new PostLikeGetLikedPostIdsQuery($userId, $ids)
        );
    }

    private function getCommented(?int $userId, array $ids): array
    {
        if (empty($userId)) {
            return [];
        }

        return $this->postGetCommentedPostIdsFetcher->fetch(
            new PostGetCommentedPostIdsQuery($userId, $ids)
        );
    }

    private function getReposted(?int $userId, array $ids): array
    {
        if (empty($userId)) {
            return [];
        }

        return $this->postGetRepostedPostIdsFetcher->fetch(
            new PostGetRepostedPostIdsQuery($userId, $ids)
        );
    }

    private function mapUsers(array $items, array $users): array
    {
        /** @var array{array{userId:int|null}} $items */
        foreach ($items as $key => $item) {
            $items[$key]['user'] = null;

            if (null !== ($item['userId'] ?? null)) {
                /** @var array{id:int} $user */
                foreach ($users as $user) {
                    if ($item['userId'] === $user['id']) {
                        $items[$key]['user'] = $user;
                        break;
                    }
                }
            }

            if (isset($items[$key]['userId'])) {
                unset($items[$key]['userId']);
            }
        }

        return $items;
    }

    private function mapUnions(array $items, array $unions): array
    {
        /** @var array{array{unionId:int|null}} $items */
        foreach ($items as $key => $item) {
            $items[$key]['union'] = null;

            if (null !== ($item['unionId'] ?? null)) {
                /** @var array{id:int} $union */
                foreach ($unions as $union) {
                    if ($item['unionId'] === $union['id']) {
                        $items[$key]['union'] = $union;
                        break;
                    }
                }
            }

            if (isset($items[$key]['unionId'])) {
                unset($items[$key]['unionId']);
            }
        }

        return $items;
    }

    private function mapPhotos(array $items, array $photos): array
    {
        /** @var array{array{photoIds:int[]}} $items */
        foreach ($items as $key => $item) {
            $items[$key]['photos'] = [];

            foreach ($item['photoIds'] ?? [] as $photoId) {
                /** @var array{id:int} $photo */
                foreach ($photos as $photo) {
                    if ($photoId === $photo['id']) {
                        $items[$key]['photos'][] = $photo;
                    }
                }
            }

            if (isset($items[$key]['photoIds'])) {
                unset($items[$key]['photoIds']);
            }
        }

        return $items;
    }

    private function mapAudios(array $items, array $audios): array
    {
        /** @var array{array{audioIds:int[]}} $items */
        foreach ($items as $key => $item) {
            $items[$key]['audios'] = [];

            foreach ($item['audioIds'] ?? [] as $audioId) {
                /** @var array{id:int} $audio */
                foreach ($audios as $audio) {
                    if ($audioId === $audio['id']) {
                        $items[$key]['audios'][] = $audio;
                    }
                }
            }

            if (isset($items[$key]['audioIds'])) {
                unset($items[$key]['audioIds']);
            }
        }

        return $items;
    }

    private function mapAudioAlbums(array $items, array $albums): array
    {
        /** @var array{array{albumId:int|null}} $items */
        foreach ($items as $key => $item) {
            $items[$key]['album'] = null;

            if (null !== $item['albumId']) {
                /** @var array{id:int} $album */
                foreach ($albums as $album) {
                    if ($item['albumId'] === $album['id']) {
                        $items[$key]['album'] = $album;
                        break;
                    }
                }
            }

            if (isset($items[$key]['albumId'])) {
                unset($items[$key]['albumId']);
            }
        }

        return $items;
    }

    private function mapVideos(array $items, array $videos): array
    {
        /** @var array{array{videoIds:int[]}} $items */
        foreach ($items as $key => $item) {
            $items[$key]['videos'] = [];

            foreach ($item['videoIds'] ?? [] as $videoId) {
                /** @var array{id:int} $video */
                foreach ($videos as $video) {
                    if ($videoId === $video['id']) {
                        $items[$key]['videos'][] = $video;
                    }
                }
            }

            if (isset($items[$key]['videoIds'])) {
                unset($items[$key]['videoIds']);
            }
        }

        return $items;
    }

    private function mapFlows(array $items, array $flows): array
    {
        /** @var array{array{flowId:int|null}} $items */
        foreach ($items as $key => $item) {
            $items[$key]['flow'] = null;

            /** @var array{id:int} $flow */
            foreach ($flows as $flow) {
                if ($item['flowId'] === $flow['id']) {
                    $items[$key]['flow'] = $flow;
                }
            }

            if (isset($items[$key]['flowId'])) {
                unset($items[$key]['flowId']);
            }
        }

        return $items;
    }

    private function mapPosts(
        array $items,
        array $posts,
        array $users,
        array $unions,
        array $photos,
        array $audios,
        array $videos,
        array $flows,
    ): array {
        /** @var array{array{postId:int|null}} $items */
        foreach ($items as $key => $item) {
            $items[$key]['post'] = null;

            if (null !== ($item['postId'] ?? null)) {
                /** @var array{id:int} $post */
                foreach ($posts as $post) {
                    if ($item['postId'] === $post['id']) {
                        $items[$key]['post'] = $post;

                        $items[$key]['post'] = (array)$this->mapUsers([$items[$key]['post']], $users)[0];
                        $items[$key]['post'] = (array)$this->mapUnions([$items[$key]['post']], $unions)[0];
                        $items[$key]['post'] = (array)$this->mapPhotos([$items[$key]['post']], $photos)[0];
                        $items[$key]['post'] = (array)$this->mapAudios([$items[$key]['post']], $audios)[0];
                        $items[$key]['post'] = (array)$this->mapVideos([$items[$key]['post']], $videos)[0];
                        $items[$key]['post'] = (array)$this->mapFlows([$items[$key]['post']], $flows)[0];

                        break;
                    }
                }
            }

            if (isset($items[$key]['postId'])) {
                unset($items[$key]['postId']);
            }
        }

        return $items;
    }

    private function mapPermissions(?int $userId, array $items): array
    {
        if (empty($userId)) {
            return $items;
        }

        /** @var array{
         *     array{
         *         userId:int,
         *         likes:array{canView:bool},
         *         comments:array{canView:bool,
         *         canComment:bool},
         *         reposts:array{canRepost:bool},
         *         time:int,
         *         canEdit:bool,
         *         canManage:bool,
         *         canDelete:bool,
         *         closeComments:bool
         *     }
         * } $items
         */
        foreach ($items as $key => $item) {
            $can = ($item['userId'] === $userId);

            $items[$key]['likes']['canView']        = $can;
            $items[$key]['comments']['canView']     = $can;
            $items[$key]['comments']['canComment']  = !$items[$key]['closeComments'];
            $items[$key]['reposts']['canRepost']    = $can;
            $items[$key]['canEdit']                 = time() - $items[$key]['time'] < 7 * 24 * 60 * 60;
            $items[$key]['canManage']               = $can;
            $items[$key]['canDelete']               = $can;

            // todo
            $items[$key]['comments']['canComment']  = !$items[$key]['closeComments'];
        }

        // todo: check permissions
        //        $items[$key]['comments']['canView']     = true;
        //        $items[$key]['comments']['canComment']  = true;
        //        $items[$key]['reposts']['canRepost']    = true;

        return $items;
    }

    private function mapLiked(array $items, array $liked): array
    {
        /** @var array{array{id:int,likes:array{isLiked:bool}}} $items */
        foreach ($items as $key => $item) {
            if (\in_array($item['id'], $liked, true)) {
                $items[$key]['likes']['isLiked'] = true;
            } else {
                $items[$key]['likes']['isLiked'] = false;
            }
        }

        return $items;
    }

    private function mapCommented(array $items, array $commented): array
    {
        /** @var array{array{id:int,comments:array{isCommented:bool}}} $items */
        foreach ($items as $key => $item) {
            if (\in_array($item['id'], $commented, true)) {
                $items[$key]['comments']['isCommented'] = true;
            } else {
                $items[$key]['comments']['isCommented'] = false;
            }
        }

        return $items;
    }

    private function mapReposted(array $items, array $reposted): array
    {
        /** @var array{array{id:int,reposts:array{isReposted:bool}}} $items */
        foreach ($items as $key => $item) {
            if (\in_array($item['id'], $reposted, true)) {
                $items[$key]['reposts']['isReposted'] = true;
            } else {
                $items[$key]['reposts']['isReposted'] = false;
            }

            $items[$key]['reposts']['canRepost']  = false;
        }

        return $items;
    }

    /** @return array{repostIds:int[],postIds:int[],flowIds:int[],unionIds:int[],userIds:int[],photoIds:int[],audioIds:int[],videoIds:int[]} */
    private function getEntityIds(array $items): array
    {
        $repostIds  = [];
        $postIds    = [];
        $flowIds    = [];
        $unionIds   = [];
        $userIds    = [];
        $photoIds   = [];
        $audioIds   = [];
        $videoIds   = [];

        /** @var array{id:int,userId:int,unionId:int,photoIds:int[],audioIds:int[],videoIds:int[],postId:int|null,flowId:int|null} $item */
        foreach ($items as $item) {
            if (isset($item['id'])) {
                $postIds[] = $item['id'];
            }

            if (isset($item['userId'])) {
                $userIds[] = $item['userId'];
            }

            if (!empty($item['unionId'])) {
                $unionIds[] = $item['unionId'];
            }

            if (isset($item['photoIds'])) {
                foreach ($item['photoIds'] as $photoId) {
                    $photoIds[] = $photoId;
                }
            }

            if (isset($item['audioIds'])) {
                foreach ($item['audioIds'] as $audioId) {
                    $audioIds[] = $audioId;
                }
            }

            if (isset($item['videoIds'])) {
                foreach ($item['videoIds'] as $videoId) {
                    $videoIds[] = $videoId;
                }
            }

            if (isset($item['postId'])) {
                $repostIds[] = $item['postId'];
            }

            if (isset($item['flowId'])) {
                $flowIds[] = $item['flowId'];
            }
        }

        return [
            'repostIds'  => array_unique($repostIds),
            'postIds'    => array_unique($postIds),
            'flowIds'    => array_unique($flowIds),
            'unionIds'   => array_unique($unionIds),
            'userIds'    => array_unique($userIds),
            'photoIds'   => array_unique($photoIds),
            'audioIds'   => array_unique($audioIds),
            'videoIds'   => array_unique($videoIds),
        ];
    }

    /** @return array{unionIds:int[],userIds:int[]} */
    private function getFlowEntityIds(array $items): array
    {
        $userIds    = [];
        $unionIds   = [];

        /** @var array{userId:int,unionId:int} $item */
        foreach ($items as $item) {
            if (isset($item['userId'])) {
                $userIds[] = $item['userId'];
            }

            if (!empty($item['unionId'])) {
                $unionIds[] = $item['unionId'];
            }
        }

        return [
            'userIds'    => array_unique($userIds),
            'unionIds'   => array_unique($unionIds),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Action\Unifier\Post;

use App\Modules\Audio\Query\Audio\GetByIds\AudioGetByIdsFetcher;
use App\Modules\Audio\Query\Audio\GetByIds\AudioGetByIdsQuery;
use App\Modules\Audio\Query\AudioAlbum\GetByIds\AudioAlbumGetByIdsFetcher;
use App\Modules\Audio\Query\AudioAlbum\GetByIds\AudioAlbumGetByIdsQuery;
use App\Modules\Audio\Service\AudioAlbumSerializer;
use App\Modules\Audio\Service\AudioSerializer;
use App\Modules\Identity\Query\GetByIds\IdentityGetByIdsFetcherCached;
use App\Modules\Identity\Query\GetByIds\IdentityGetByIdsQuery;
use App\Modules\Identity\Service\UserSerializer;
use App\Modules\Media\Query\Video\GetByIds\VideoGetByIdsFetcher;
use App\Modules\Media\Query\Video\GetByIds\VideoGetByIdsQuery;
use App\Modules\Media\Service\VideoSerializer;
use App\Modules\Photo\Query\Photo\GetByIds\PhotoGetByIdsFetcher;
use App\Modules\Photo\Query\Photo\GetByIds\PhotoGetByIdsQuery;
use App\Modules\Photo\Service\PhotoSerializer;
use App\Modules\Post\Query\PostCommentLike\GetLikedCommentIds\PostCommentLikeGetLikedCommentIdsFetcher;
use App\Modules\Post\Query\PostCommentLike\GetLikedCommentIds\PostCommentLikeGetLikedCommentIdsQuery;
use App\Modules\Post\Service\PostCommentSerializer;
use App\Modules\Union\Query\Union\GetByIds\UnionGetByIdsFetcherCached;
use App\Modules\Union\Query\Union\GetByIds\UnionGetByIdsQuery;
use App\Modules\Union\Service\UnionSerializer;
use ZayMedia\Shared\Http\Unifier\UnifierInterface;

final readonly class PostCommentUnifier implements UnifierInterface
{
    public function __construct(
        private PostCommentSerializer $postCommentSerializer,
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
        private PostCommentLikeGetLikedCommentIdsFetcher $postCommentLikeGetLikedCommentIdsFetcher
    ) {}

    public function unifyOne(?int $userId, ?array $item): array
    {
        /** @var array{array} $result */
        $result = $this->unify($userId, (null !== $item) ? [$item] : []);
        return (isset($result[0])) ? $result[0] : [];
    }

    public function unify(?int $userId, array $items): array
    {
        $items = $this->postCommentSerializer->serializeItems($items);

        $items = $this->mapPermissions($userId, $items);

        $entityIds = $this->getEntityIds($items);

        $items = $this->mapUsers($items, $this->getUsers($entityIds['userIds']));

        $items = $this->mapUnions($items, $this->getUnions($entityIds['unionIds']));

        $items = $this->mapPhotos($items, $this->getPhotos($entityIds['photoIds']));

        $items = $this->mapAudios($items, $this->getAudios($entityIds['audioIds']));

        $items = $this->mapVideos($items, $this->getVideos($entityIds['videoIds']));

        return $this->mapLiked($items, $this->getLiked($userId, $entityIds['itemIds']));
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
            if (isset($item['albumId']) && !empty($item['albumId'])) {
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

    private function getLiked(?int $userId, array $ids): array
    {
        if (empty($userId)) {
            return [];
        }

        return $this->postCommentLikeGetLikedCommentIdsFetcher->fetch(
            new PostCommentLikeGetLikedCommentIdsQuery($userId, $ids)
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

    private function mapPermissions(?int $userId, array $items): array
    {
        if (empty($userId)) {
            return $items;
        }

        /** @var array{array{userId:int,likes:array{canView:bool},comments:array{canView:bool,canComment:bool},reposts:array{canRepost:bool},canEdit:bool,canDelete:bool}} $items */
        foreach ($items as $key => $item) {
            if ($item['userId'] === $userId) {
                $items[$key]['likes']['canView']        = true;
                $items[$key]['canEdit']                 = true;
                $items[$key]['canDelete']               = true;
            }

            $items[$key]['comments']['canComment'] = true;
        }

        return $items;
    }

    private function mapLiked(array $items, array $liked): array
    {
        /** @var array{array{id:int,likes:array{isLiked:bool}}} $items */
        foreach ($items as $key => $item) {
            if (\in_array($item['id'], $liked, true)) {
                $items[$key]['likes']['isLiked'] = true;
            }
        }

        return $items;
    }

    /** @return array{itemIds:int[],unionIds:int[],userIds:int[],photoIds:int[],audioIds:int[],videoIds:int[]} */
    private function getEntityIds(array $items): array
    {
        $itemIds  = [];
        $unionIds = [];
        $userIds  = [];
        $photoIds = [];
        $audioIds = [];
        $videoIds = [];

        /** @var array{id:int,userId:int,unionId:int,photoIds:int[],audioIds:int[],videoIds:int[]} $item */
        foreach ($items as $item) {
            if (isset($item['id'])) {
                $itemIds[] = $item['id'];
            }

            if (isset($item['userId'])) {
                $userIds[] = $item['userId'];
            }

            if (isset($item['unionId']) && !empty($item['unionId'])) {
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
        }

        return [
            'itemIds'    => array_unique($itemIds),
            'unionIds'   => array_unique($unionIds),
            'userIds'    => array_unique($userIds),
            'photoIds'   => array_unique($photoIds),
            'audioIds'   => array_unique($audioIds),
            'videoIds'   => array_unique($videoIds),
        ];
    }
}

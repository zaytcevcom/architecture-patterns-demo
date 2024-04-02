<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Helpers;

use App\Modules\Flow\Entity\Flow\Flow;
use App\Modules\Flow\Entity\FlowComment\FlowComment;
use App\Modules\Identity\Entity\User\User;
use App\Modules\Media\Entity\Video\Video;
use App\Modules\Media\Entity\Video\VideoRepository;
use App\Modules\Media\Entity\VideoComment\VideoComment;
use App\Modules\Media\Service\VideoSerializer;
use App\Modules\Photo\Entity\Photo\Photo;
use App\Modules\Photo\Entity\Photo\PhotoRepository;
use App\Modules\Photo\Entity\PhotoComment\PhotoComment;
use App\Modules\Photo\Service\PhotoSerializer;
use App\Modules\Post\Entity\Post\Post;
use App\Modules\Post\Entity\PostComment\PostComment;
use ZayMedia\Shared\Components\Transliterator\Transliterator;

use function App\Components\env;

final readonly class NotifierHelper
{
    public function __construct(
        private PhotoRepository $photoRepository,
        private VideoRepository $videoRepository,
        private Transliterator $transliterator,
        private PhotoSerializer $photoSerializer,
        private VideoSerializer $videoSerializer,
    ) {}

    public function getThreadName(NotifierCategory $category, int $itemId): string
    {
        $type = match ($category) {
            NotifierCategory::BADGE,
            NotifierCategory::PUSH_REMOVE => 'hide',

            NotifierCategory::CONTACT_ADDED,
            NotifierCategory::CONTACT_NEW_REQUEST => 'contact',

            NotifierCategory::PHOTO_COMMENT_ANSWERED,
            NotifierCategory::PHOTO_COMMENTED,
            NotifierCategory::PHOTO_COMMENT_LIKED,
            NotifierCategory::PHOTO_LIKED => 'photo',

            NotifierCategory::POST_COMMENT_ANSWERED,
            NotifierCategory::POST_COMMENTED,
            NotifierCategory::POST_COMMENT_LIKED,
            NotifierCategory::POST_LIKED,
            NotifierCategory::POST_REPOSTED,
            NotifierCategory::POST_PUBLISHED => 'post',

            NotifierCategory::VIDEO_COMMENT_ANSWERED,
            NotifierCategory::VIDEO_COMMENTED,
            NotifierCategory::VIDEO_COMMENT_LIKED,
            NotifierCategory::VIDEO_LIKED => 'video',

            NotifierCategory::FLOW_COMMENT_ANSWERED,
            NotifierCategory::FLOW_COMMENTED,
            NotifierCategory::FLOW_COMMENT_LIKED,
            NotifierCategory::FLOW_LIKED,
            NotifierCategory::FLOW_REPOSTED => 'flow',

            NotifierCategory::CONVERSATION_NEW_MESSAGE => 'message'
        };

        return $type . ':' . $itemId;
    }

    public function getId(NotifierCategory $category, int $itemId): int
    {
        $main = match ($category) {
            NotifierCategory::BADGE,
            NotifierCategory::PUSH_REMOVE               => 0,
            NotifierCategory::CONTACT_ADDED             => 1,
            NotifierCategory::CONTACT_NEW_REQUEST       => 2,
            NotifierCategory::PHOTO_COMMENT_ANSWERED    => 3,
            NotifierCategory::PHOTO_COMMENTED           => 4,
            NotifierCategory::PHOTO_COMMENT_LIKED       => 5,
            NotifierCategory::PHOTO_LIKED               => 6,
            NotifierCategory::POST_COMMENT_ANSWERED     => 7,
            NotifierCategory::POST_COMMENTED            => 8,
            NotifierCategory::POST_COMMENT_LIKED        => 9,
            NotifierCategory::POST_LIKED                => 10,
            NotifierCategory::POST_REPOSTED             => 11,
            NotifierCategory::POST_PUBLISHED            => 12,
            NotifierCategory::VIDEO_COMMENT_ANSWERED    => 13,
            NotifierCategory::VIDEO_COMMENTED           => 14,
            NotifierCategory::VIDEO_COMMENT_LIKED       => 15,
            NotifierCategory::VIDEO_LIKED               => 16,
            NotifierCategory::FLOW_COMMENT_ANSWERED     => 17,
            NotifierCategory::FLOW_COMMENTED            => 18,
            NotifierCategory::FLOW_COMMENT_LIKED        => 19,
            NotifierCategory::FLOW_LIKED                => 20,
            NotifierCategory::FLOW_REPOSTED             => 21,
            NotifierCategory::CONVERSATION_NEW_MESSAGE  => 22,
        };

        return $main * 1_000_000_000_000_000 + $itemId;
    }

    public function getUserLink(int $userId): string
    {
        return User::getWebUrl($userId);
    }

    public function getPhotoLink(int $photoId): string
    {
        return Photo::getAppUrl($photoId);
    }

    public function getPostLink(int $postId): string
    {
        return Post::getAppUrl($postId);
    }

    public function getFlowLink(int $postId): string
    {
        return Flow::getAppUrl($postId);
    }

    public function getVideoLink(int $videoId): string
    {
        return Video::getAppUrl($videoId);
    }

    public function getMessageLink(int $conversationId, int $messageId): string
    {
        return 'lo://conversations/' . $conversationId . '/' . $messageId;
    }

    public function getPostAttachmentUrl(Post $post): ?string
    {
        if ($photoId = explode(',', $post->getPhotoIds() ?? '')[0] ?? null) {
            if ($photo = $this->photoRepository->findById((int)$photoId)) {
                return $this->getPhotoAttachmentUrl($photo);
            }
        }

        if ($videoId = explode(',', $post->getVideoIds() ?? '')[0] ?? null) {
            if ($video = $this->videoRepository->findById((int)$videoId)) {
                return $this->getVideoAttachmentUrl($video);
            }
        }

        return null;
    }

    public function getFlowAttachmentUrl(Flow $flow): ?string
    {
        return $flow->getPhoto()?->getValue();
    }

    public function getPostText(Post $post): string
    {
        if ($text = $post->getMessage()) {
            return $text;
        }

        if ($post->getPhotoIds()) {
            return 'PHOTO_STUB';
        }

        if ($post->getVideoIds()) {
            return 'VIDEO_STUB';
        }

        return '';
    }

    public function getFlowText(Flow $flow): string
    {
        if ($text = $flow->getDescription()) {
            return $text;
        }

        return '';
    }

    public function getPhotoAttachmentUrl(Photo $photo): ?string
    {
        $photo = $photo->getPhoto()?->getValue() ?? null;

        if (null === $photo) {
            return null;
        }

        /** @var array{src: string}|string|null $url */
        $url = $this->photoSerializer->getPhoto(['photo' => $photo])['xs'] ?? null;

        if (\is_array($url)) {
            $url = $url['src'] ?? null;
        }

        return $url;
    }

    public function getVideoAttachmentUrl(Video $video): ?string
    {
        $photo = $video->getPhoto();

        if (null === $photo) {
            return null;
        }

        /** @var array{src: string}|string|null $url */
        $url = $this->videoSerializer->getPhoto(['photo' => $photo])['xs'] ?? null;

        if (\is_array($url)) {
            $url = $url['src'] ?? null;
        }

        return $url;
    }

    public function getCommentAttachmentUrl(FlowComment|PhotoComment|PostComment|VideoComment $comment): ?string
    {
        if ($photoId = explode(',', $comment->getPhotoIds() ?? '')[0] ?? null) {
            if ($photo = $this->photoRepository->findById((int)$photoId)) {
                return $this->getPhotoAttachmentUrl($photo);
            }
        }

        if ($videoId = explode(',', $comment->getVideoIds() ?? '')[0] ?? null) {
            if ($video = $this->videoRepository->findById((int)$videoId)) {
                return $this->getVideoAttachmentUrl($video);
            }
        }

        return null;
    }

    public function getCommentText(FlowComment|PhotoComment|PostComment|VideoComment $comment): string
    {
        if ($text = $comment->getMessage()) {
            return $text;
        }

        if ($comment->getPhotoIds()) {
            return 'PHOTO_STUB';
        }

        if ($comment->getVideoIds()) {
            return 'VIDEO_STUB';
        }

        return '';
    }

    public function getUserPhoto(User $user): string
    {
        /** @var array{src: string}|string|null $url */
        $url = User::getPhotoParsed($user->getPhoto()?->getValue())['xs'] ?? null;

        if (\is_array($url)) {
            $url = $url['src'] ?? null;
        }

        if (\is_string($url)) {
            return $url;
        }

        return env('SCHEME') . '://' . env('DOMAIN') . '/files/stubs/stub-user.png';
    }

    public function getFirstName(User $user, string $locale): string
    {
        if (
            !$this->transliterator->isCyrillicLocale($locale) &&
            $firstName = $user->getFirstNameTranslit()?->getValue()
        ) {
            return $firstName;
        }
        return $user->getFirstName()->getValue();
    }

    public function getLastName(User $user, string $locale): string
    {
        if (
            !$this->transliterator->isCyrillicLocale($locale) &&
            $lastName = $user->getLastNameTranslit()?->getValue()
        ) {
            return $lastName;
        }
        return $user->getLastName()->getValue();
    }
}

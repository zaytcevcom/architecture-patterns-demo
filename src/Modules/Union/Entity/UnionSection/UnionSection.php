<?php

declare(strict_types=1);

namespace App\Modules\Union\Entity\UnionSection;

use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity]
#[ORM\Table(name: 'union_section')]
class UnionSection
{
    #[ORM\Id]
    #[ORM\Column(type: 'bigint', unique: true, options: ['unsigned' => true])]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $unionId;

    #[ORM\Column(type: 'boolean')]
    private bool $posts;

    #[ORM\Column(type: 'boolean')]
    private bool $photos;

    #[ORM\Column(type: 'boolean')]
    private bool $videos;

    #[ORM\Column(type: 'boolean')]
    private bool $audios;

    #[ORM\Column(type: 'boolean')]
    private bool $contacts;

    #[ORM\Column(type: 'boolean')]
    private bool $links;

    #[ORM\Column(type: 'boolean')]
    private bool $messages;

    public function __construct(
        int $unionId,
        bool $posts,
        bool $photos,
        bool $videos,
        bool $audios,
        bool $contacts,
        bool $links,
        bool $messages,
    ) {
        $this->unionId = $unionId;
        $this->posts = $posts;
        $this->photos = $photos;
        $this->videos = $videos;
        $this->audios = $audios;
        $this->contacts = $contacts;
        $this->links = $links;
        $this->messages = $messages;
    }

    public static function create(
        int $unionId,
    ): self {
        return new self(
            unionId: $unionId,
            posts: true,
            photos: true,
            videos: true,
            audios: true,
            contacts: true,
            links: true,
            messages: true,
        );
    }

    public function edit(
        bool $posts,
        bool $photos,
        bool $videos,
        bool $audios,
        bool $contacts,
        bool $links,
        bool $messages,
    ): void {
        $this->posts = $posts;
        $this->photos = $photos;
        $this->videos = $videos;
        $this->audios = $audios;
        $this->contacts = $contacts;
        $this->links = $links;
        $this->messages = $messages;
    }

    public function getId(): int
    {
        if (null === $this->id) {
            throw new DomainException('Id not set');
        }
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getUnionId(): int
    {
        return $this->unionId;
    }

    public function setUnionId(int $unionId): void
    {
        $this->unionId = $unionId;
    }

    public function isPosts(): bool
    {
        return $this->posts;
    }

    public function setPosts(bool $posts): void
    {
        $this->posts = $posts;
    }

    public function isPhotos(): bool
    {
        return $this->photos;
    }

    public function setPhotos(bool $photos): void
    {
        $this->photos = $photos;
    }

    public function isVideos(): bool
    {
        return $this->videos;
    }

    public function setVideos(bool $videos): void
    {
        $this->videos = $videos;
    }

    public function isAudios(): bool
    {
        return $this->audios;
    }

    public function setAudios(bool $audios): void
    {
        $this->audios = $audios;
    }

    public function isContacts(): bool
    {
        return $this->contacts;
    }

    public function setContacts(bool $contacts): void
    {
        $this->contacts = $contacts;
    }

    public function isLinks(): bool
    {
        return $this->links;
    }

    public function setLinks(bool $links): void
    {
        $this->links = $links;
    }

    public function isMessages(): bool
    {
        return $this->messages;
    }

    public function setMessages(bool $messages): void
    {
        $this->messages = $messages;
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Identity\Service;

use ZayMedia\Shared\Components\Realtime\Realtime;

use function App\Components\env;

final class IdentityRealtimeNotifier
{
    private const PREFIX = 'identity-';

    private const TYPE_UPDATE_COUNTERS  = 0;
    private const TYPE_SECTIONS_EXCLUDE = 1;

    private const TYPE_CONVERSATION_UPDATE          = 2001;
    private const TYPE_CONVERSATION_DELETE          = 2002;
    private const TYPE_CONVERSATION_RESTORE         = 2003;
    private const TYPE_CONVERSATION_PIN             = 2004;
    private const TYPE_CONVERSATION_UNPIN           = 2005;
    private const TYPE_CONVERSATION_MUTE            = 2006;
    private const TYPE_CONVERSATION_UNMUTE          = 2007;
    private const TYPE_CONVERSATION_UNREAD          = 2008;
    private const TYPE_CONVERSATION_TYPING_START    = 2009;
    private const TYPE_CONVERSATION_TYPING_STOP     = 2010;
    private const TYPE_MESSAGE_NEW                  = 2011;
    private const TYPE_MESSAGE_UPDATE               = 2012;
    private const TYPE_MESSAGE_DELETE               = 2013;
    private const TYPE_MESSAGE_RESTORE              = 2014;
    private const TYPE_MESSAGE_READ                 = 2015;
    private const TYPE_MESSAGE_PIN                  = 2016;
    private const TYPE_MESSAGE_UNPIN                = 2017;

    public function __construct(
        private readonly Realtime $realtime
    ) {}

    public static function getChannelName(int $userId): string
    {
        $env = (env('APP_ENV') !== 'production') ? 'dev-' : '';
        return $env . self::PREFIX . $userId;
    }

    public function updateCounterContacts(int $userId, int $count): void
    {
        $this->publish(
            self::TYPE_UPDATE_COUNTERS,
            $userId,
            [
                'counters' => [
                    'contacts' => $count,
                ],
            ]
        );
    }

    public function updateCounterCommunities(int $userId, int $count): void
    {
        $this->publish(
            self::TYPE_UPDATE_COUNTERS,
            $userId,
            [
                'counters' => [
                    'communities' => $count,
                ],
            ]
        );
    }

    public function updateCounterCommunication(int $userId, int $count): void
    {
        $this->publish(
            self::TYPE_UPDATE_COUNTERS,
            $userId,
            [
                'counters' => [
                    'communication' => $count,
                ],
            ]
        );
    }

    public function updateConversation(int $userId, array $data): void
    {
        $this->publish(self::TYPE_CONVERSATION_UPDATE, $userId, $data);
    }

    public function deleteConversation(int $userId, array $data): void
    {
        $this->publish(self::TYPE_CONVERSATION_DELETE, $userId, $data);
    }

    public function restoreConversation(int $userId, array $data): void
    {
        $this->publish(self::TYPE_CONVERSATION_RESTORE, $userId, $data);
    }

    public function pinConversation(int $userId, array $data): void
    {
        $this->publish(self::TYPE_CONVERSATION_PIN, $userId, $data);
    }

    public function unpinConversation(int $userId, array $data): void
    {
        $this->publish(self::TYPE_CONVERSATION_UNPIN, $userId, $data);
    }

    public function muteConversation(int $userId, array $data): void
    {
        $this->publish(self::TYPE_CONVERSATION_MUTE, $userId, $data);
    }

    public function unmuteConversation(int $userId, array $data): void
    {
        $this->publish(self::TYPE_CONVERSATION_UNMUTE, $userId, $data);
    }

    public function unreadConversation(int $userId, array $data): void
    {
        $this->publish(self::TYPE_CONVERSATION_UNREAD, $userId, $data);
    }

    public function typingStartConversation(int $userId, array $data): void
    {
        $this->publish(self::TYPE_CONVERSATION_TYPING_START, $userId, $data);
    }

    public function typingStopConversation(int $userId, array $data): void
    {
        $this->publish(self::TYPE_CONVERSATION_TYPING_STOP, $userId, $data);
    }

    public function newMessage(int $userId, array $data): void
    {
        $this->publish(self::TYPE_MESSAGE_NEW, $userId, $data);
    }

    public function updateMessage(int $userId, array $data): void
    {
        $this->publish(self::TYPE_MESSAGE_UPDATE, $userId, $data);
    }

    public function deleteMessage(int $userId, array $data): void
    {
        $this->publish(self::TYPE_MESSAGE_DELETE, $userId, $data);
    }

    public function restoreMessage(int $userId, array $data): void
    {
        $this->publish(self::TYPE_MESSAGE_RESTORE, $userId, $data);
    }

    public function readMessage(int $userId, array $data): void
    {
        $this->publish(self::TYPE_MESSAGE_READ, $userId, $data);
    }

    public function pinMessage(int $userId, array $data): void
    {
        $this->publish(self::TYPE_MESSAGE_PIN, $userId, $data);
    }

    public function unPinMessage(int $userId, array $data): void
    {
        $this->publish(self::TYPE_MESSAGE_UNPIN, $userId, $data);
    }

    private function publish(int $type, int $userId, ?array $payload): void
    {
        $this->realtime->publish(
            channel: self::getChannelName($userId),
            data: [
                'type'      => $type,
                'payload'   => $payload,
            ]
        );
    }
}

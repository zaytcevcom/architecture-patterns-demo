<?php

declare(strict_types=1);

namespace App\Modules\Notifier\Service\Pusher;

use App\Modules\Notifier\Helpers\NotifierCategory;
use DateTime;
use GuzzleHttp\Client;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

use function App\Components\env;

final readonly class Pusher
{
    public function __construct(
        private TranslatorInterface $translator,
        private Client $client
    ) {}

    public function send(Command $command): void
    {
        if (empty($command->tokens)) {
            return;
        }

        if ($subtitle = $command->subtitle) {
            $subtitle = $this->translate($subtitle, $command->translateParams, $command->locale);
        }

        $dataTranslated = $command->data;

        if (isset($dataTranslated['locale']) && \is_array($dataTranslated['locale'])) {
            $locale = [];

            /**
             * @var string $key
             * @var string $value
             */
            foreach ($dataTranslated['locale'] as $key => $value) {
                $locale[$key] = $this->translate($value, [], $command->locale);
            }

            $dataTranslated['locale'] = $locale;
        }

        $title = $this->translate($command->title, $command->translateParams, $command->locale);
        $body = $this->translate($command->body, $command->translateParams, $command->locale);

        $platform = $this->platformByString($command->platform);

        if ($platform === Platform::IOS) {
            $notification = [
                'mutable_content' => true,
                'topic' => $command->bundleId,
                'alert' => [
                    'title'     => $title,
                    'subtitle'  => $subtitle,
                    'body'      => $body,
                ],
                'thread-id' => $command->thread,
                'data' => [
                    'data' => $dataTranslated,
                ],
                'category'    => $command->category,
                'badge'       => $command->badge,
                'sound' => [
                    'name' => $command->sound,
                ],
            ];
        } else {
            $notification = [
                'data' => [
                    'title'     => $title,
                    'subtitle'  => $subtitle,
                    'body'      => $body,
                    'badge'     => $command->badge,
                    'thread'    => $command->thread,
                    'category'  => $command->category,
                    'image'     => $dataTranslated['attachmentUrl'] ?? null,
                    'data'      => $dataTranslated,
                    'sound'     => $command->sound,
                ],
            ];
        }

        $this->push($platform, $command->tokens, $notification);
    }

    public function sendBadge(BadgeCommand $command): void
    {
        if (empty($command->tokens)) {
            return;
        }

        $platform = $this->platformByString($command->platform);

        if ($platform === Platform::IOS) {
            $notification = [
                'mutable_content' => true,
                'topic' => $command->bundleId,
                'alert' => [
                    'title'     => '',
                    'body'      => '',
                ],
                'category'    => NotifierCategory::BADGE,
                'badge'       => $command->badge,
            ];
        } else {
            $notification = [
                'data' => [
                    'title'     => '',
                    'body'      => '',
                    'category'  => NotifierCategory::BADGE,
                    'badge'     => $command->badge,
                ],
            ];
        }

        $this->push($platform, $command->tokens, $notification);
    }

    public function sendHide(HideCommand $command): void
    {
        if (empty($command->tokens)) {
            return;
        }

        $platform = $this->platformByString($command->platform);

        if ($platform === Platform::IOS) {
            $notification = [
                'mutable_content' => true,
                'topic' => $command->bundleId,
                'alert' => [
                    'title'     => '',
                    'body'      => '',
                ],
                'data' => [
                    'data' => $command->data,
                ],
                'category' => $command->category,
            ];
        } else {
            $notification = [
                'data' => [
                    'title'     => '',
                    'body'      => '',
                    'category'  => $command->category,
                    'data'      => $command->data,
                ],
            ];
        }

        $this->push($platform, $command->tokens, $notification);
    }

    public function sendVoIP(VoIPCommand $command): void
    {
        if (empty($command->tokens)) {
            return;
        }

        $platform = $this->platformByString($command->platform);

        if ($platform === Platform::IOS) {
            $notification = [
                'content_available' => true,
                'topic' => $command->bundleId . '.voip',
                'push_type' => 'voip',
                'expiration' => (new DateTime('+30 seconds'))->getTimestamp(),
                'data' => [
                    'data' => $command->data,
                ],
            ];
        } else {
            $notification = [
                'data' => [
                    // todo
                    'data'      => $command->data,
                ],
            ];
        }

        $this->push($platform, $command->tokens, $notification);
    }

    /**
     * @param array{bundleId: string, platform: string, locale: string, token: string, voip_token: string}[] $tokens
     * @return array{bundleId: string, platform: string, locale: string, tokens: string[]}[]|empty[]
     */
    public function getGroupedTokens(array $tokens): array
    {
        $items = [];

        foreach ($tokens as $info) {
            if (empty($info['token'])) {
                continue;
            }

            $isFound = false;

            foreach ($items as $key => $value) {
                if (
                    isset($value['bundleId']) && $value['bundleId'] === $info['bundleId'] &&
                    isset($value['platform']) && $value['platform'] === $info['platform'] &&
                    isset($value['locale']) && $value['locale'] === $info['locale']
                ) {
                    $items[$key]['tokens'][] = $info['token'];
                    $isFound = true;
                    break;
                }
            }

            if ($isFound) {
                continue;
            }

            $items[] = [
                'bundleId'  => $info['bundleId'],
                'platform'  => $info['platform'],
                'locale'    => $info['locale'],
                'tokens'    => [
                    $info['token'],
                ],
            ];
        }

        /** @var array{bundleId: string, platform: string, locale: string, tokens: string[]}[]|empty[] $items */
        return $items;
    }

    /**
     * @param array{bundleId: string, platform: string, locale: string, token: string, voip_token: string}[] $tokens
     * @return array{bundleId: string, platform: string, locale: string, tokens: string[]}[]
     */
    public function getGroupedVoipTokens(array $tokens): array
    {
        $items = [];

        foreach ($tokens as $info) {
            if (empty($info['voip_token'])) {
                continue;
            }

            $isFound = false;

            foreach ($items as $key => $value) {
                if (
                    isset($value['bundleId']) && $value['bundleId'] === $info['bundleId'] &&
                    isset($value['platform']) && $value['platform'] === $info['platform'] &&
                    isset($value['locale']) && $value['locale'] === $info['locale']
                ) {
                    $items[$key]['tokens'][] = $info['token'];
                    $isFound = true;
                    break;
                }
            }

            if ($isFound) {
                continue;
            }

            $items[] = [
                'bundleId'  => $info['bundleId'],
                'platform'  => $info['platform'],
                'locale'    => $info['locale'],
                'tokens'    => [
                    $info['voip_token'],
                ],
            ];
        }

        /** @var array{bundleId: string, platform: string, locale: string, tokens: string[]}[]|empty $items */
        return $items;
    }

    public function translate(string $text, array $params, string $locale): string
    {
        return $this->translator->trans($text, $params, 'notifier', $locale);
    }

    private function push(int $platform, array $tokens, array $notification): void
    {
        try {
            $host = env('NOTIFICATION_HOST');

            $this->client->request(
                method: 'POST',
                uri: $host . '/api/push',
                options: [
                    'json' => [
                        'notifications' => [
                            [
                                // 'development' => env('APP_ENV') !== 'production',
                                'platform' => $platform,
                                'tokens' => $tokens,
                                ...$notification,
                            ],
                        ],
                    ],
                ]
            );
        } catch (Throwable) {
        }
    }

    private function platformByString(string $platform): int
    {
        return strtolower($platform) === 'ios' ? Platform::IOS : Platform::ANDROID;
    }
}

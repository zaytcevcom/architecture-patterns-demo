<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Identity;

use App\Modules\Contact\Query\GetRequestsInCount\ContactsGetRequestsInCountFetcher;
use App\Modules\Data\Entity\Space\SpaceRepository;
use App\Modules\Data\Entity\SpaceCity\SpaceCityRepository;
use App\Modules\Flow\Entity\FlowSettings\FlowSettings;
use App\Modules\Flow\Entity\FlowSettings\FlowSettingsRepository;
use App\Modules\Identity\Entity\User\UserRepository;
use App\Modules\Identity\Event\User\UserEventPublisher;
use App\Modules\Identity\Event\User\UserQueue;
use App\Modules\Identity\Service\IdentityRealtimeNotifier;
use App\Modules\Identity\Service\SystemRealtimeNotifier;
use App\Modules\Messenger\Query\Conversation\GetCountUnread\ConversationGetCountUnreadFetcher;
use App\Modules\System\Query\System\GetExcludeSections\SystemGetExcludeSectionsFetcher;
use App\Modules\Union\Query\Community\GetRequestsCount\UnionGetRequestsCountFetcher;
use Doctrine\DBAL\Exception;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;
use ZayMedia\Shared\Components\Flusher;
use ZayMedia\Shared\Components\Realtime\Realtime;
use ZayMedia\Shared\Http\Middleware\Identity\Authenticate;
use ZayMedia\Shared\Http\Response\JsonDataResponse;

use function App\Components\env;

#[OA\Get(
    path: '/identity/system',
    description: 'Получение необходимой информации для клиентского приложения',
    summary: 'Получение необходимой информации для клиентского приложения',
    security: [['bearerAuth' => '{}']],
    tags: ['Identity']
)]
#[OA\Response(
    response: '200',
    description: 'Successful operation'
)]
final readonly class GetSystemAction implements RequestHandlerInterface
{
    public function __construct(
        private SystemGetExcludeSectionsFetcher $excludeSectionsFetcher,
        private ContactsGetRequestsInCountFetcher $requestsInCountFetcher,
        private UnionGetRequestsCountFetcher $unionGetRequestsCountFetcher,
        private ConversationGetCountUnreadFetcher $conversationGetCountUnreadFetcher,
        private UserRepository $userRepository,
        private FlowSettingsRepository $flowSettingsRepository,
        private SpaceRepository $spaceRepository,
        private SpaceCityRepository $spaceCityRepository,
        private Flusher $flusher,
        private Realtime $realtime,
        private UserEventPublisher $eventPublisher,
    ) {}

    /**
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $identity = Authenticate::getIdentity($request);

        $user = $this->userRepository->getById($identity->id);

        $excludeSections = $this->excludeSectionsFetcher->fetch();

        if (\in_array($identity->id, [246, 4889, 4890, 736], true)) {
            if (!\in_array('audio', $excludeSections, true)) {
                $excludeSections[] = 'audio';
            }

            if (!\in_array('communities', $excludeSections, true)) {
                $excludeSections[] = 'communities';
            }

            if (!\in_array('flows', $excludeSections, true)) {
                $excludeSections[] = 'flows';
            }
        }

        $channel        = IdentityRealtimeNotifier::getChannelName($identity->id);
        $systemChannel  = SystemRealtimeNotifier::getChannelName();

        $space = null;

        if ($spaceId = $user->getSpaceId()) {
            try {
                $spaceCity = $this->spaceCityRepository->getById($spaceId);
                $space = $this->spaceRepository->getById($spaceCity->getSpaceId());
                $space = [
                    'id' => $spaceCity->getId(),
                    'name' => $space->getName(),
                ];
            } catch (Throwable) {
            }
        }

        $result = [
            'spaceId' => $user->getSpaceId(),
            'space' => $space,
            'sections' => [
                'main' => 'profile',
                'exclude' => $excludeSections,
            ],
            'counters' => [
                'contacts'      => $this->requestsInCountFetcher->fetch($identity->id),
                'communities'   => $this->unionGetRequestsCountFetcher->fetch($identity->id),
                'communication' => $this->conversationGetCountUnreadFetcher->fetch($identity->id),
            ],
            'flowSettings' => $this->getFlowSettings($identity->id),
            'realtime' => [
                'connection'            => env('CENTRIFUGO_WS'),
                'token'                 => $this->realtime->generateConnectionToken((string)$identity->id, time() + 7 * 24 * 3600),
                'channel'               => $channel,
                'channelToken'          => $this->realtime->generateSubscriptionToken((string)$identity->id, $channel, time() + 7 * 24 * 3600),
                'systemChannel'         => SystemRealtimeNotifier::getChannelName(),
                'systemChannelToken'    => $this->realtime->generateSubscriptionToken((string)$identity->id, $systemChannel, time() + 7 * 24 * 3600),
            ],
        ];

        $this->eventPublisher->handle(UserQueue::OPENED, $user->getId());

        return new JsonDataResponse([
            $result,
        ]);
    }

    private function getFlowSettings(int $userId): array
    {
        $flowSettings = $this->flowSettingsRepository->findByUserId($userId);

        if (null === $flowSettings) {
            $flowSettings = $this->createFlowSettings($userId);
        }

        return $flowSettings->toArray();
    }

    private function createFlowSettings(int $userId): FlowSettings
    {
        $flowSettings = FlowSettings::create(
            userId: $userId,
            languageIds: [],
            categoryIds: [],
            safeMode: true,
            streamMode: true
        );

        $this->flowSettingsRepository->add($flowSettings);
        $this->flusher->flush();

        return $flowSettings;
    }
}

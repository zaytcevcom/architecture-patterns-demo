<?php

declare(strict_types=1);

use App\Http\Action;
use App\Http\Action\V1\Audios\AudioAlbums\GetAudiosAction;
use App\Http\Action\V1\Audios\AudioAlbums\NewAction;
use App\Http\Action\V1\Audios\AudioAlbums\Union\UnionGetAudioAlbumsAction;
use App\Http\Action\V1\Audios\AudioAlbums\User\UserAddAudioAlbumAction;
use App\Http\Action\V1\Audios\AudioAlbums\User\UserGetAudioAlbumsAction;
use App\Http\Action\V1\Audios\AudioAlbums\User\UserRemoveAudioAlbumAction;
use App\Http\Action\V1\Audios\AudioPlaylists\Union\UnionGetAudioPlaylistsAction;
use App\Http\Action\V1\Audios\AudioPlaylists\User\UserAddAudioPlaylistAction;
use App\Http\Action\V1\Audios\AudioPlaylists\User\UserGetAudioPlaylistsAction;
use App\Http\Action\V1\Audios\AudioPlaylists\User\UserRemoveAudioPlaylistAction;
use App\Http\Action\V1\Audios\Audios\GetBannersAction;
use App\Http\Action\V1\Audios\Audios\GetHistoryAction;
use App\Http\Action\V1\Audios\Audios\GetLyricsAction;
use App\Http\Action\V1\Audios\Audios\GetPopularAction;
use App\Http\Action\V1\Audios\Audios\ListenAction;
use App\Http\Action\V1\Audios\Audios\Union\UnionGetAudiosAction;
use App\Http\Action\V1\Audios\Audios\User\UserAddAudioAction;
use App\Http\Action\V1\Audios\Audios\User\UserGetAudiosAction;
use App\Http\Action\V1\Audios\Audios\User\UserRemoveAudioAction;
use App\Http\Action\V1\AuthAction;
use App\Http\Action\V1\Contacts\AddAction;
use App\Http\Action\V1\Contacts\ClearSearchHistoryAction;
use App\Http\Action\V1\Contacts\GetBirthdayAction;
use App\Http\Action\V1\Contacts\GetByUserIdAction;
use App\Http\Action\V1\Contacts\GetByUserIdPreviewAction;
use App\Http\Action\V1\Contacts\GetImportantAction;
use App\Http\Action\V1\Contacts\GetMutualAction;
use App\Http\Action\V1\Contacts\GetOnlineByUserIdAction;
use App\Http\Action\V1\Contacts\GetRecommendationsAction;
use App\Http\Action\V1\Contacts\GetRelationshipAction;
use App\Http\Action\V1\Contacts\GetRequestsInAction;
use App\Http\Action\V1\Contacts\GetRequestsOutAction;
use App\Http\Action\V1\Contacts\GetSearchHistoryAction;
use App\Http\Action\V1\Contacts\GetSubscribersAction;
use App\Http\Action\V1\Contacts\ImportAction;
use App\Http\Action\V1\Contacts\RemoveAction;
use App\Http\Action\V1\Contacts\RollbackAction;
use App\Http\Action\V1\Contacts\ViewAction;
use App\Http\Action\V1\Data\GetCitiesAction;
use App\Http\Action\V1\Data\GetCitiesWithSpacesAction;
use App\Http\Action\V1\Data\GetCountriesAction;
use App\Http\Action\V1\Data\GetLanguageByCodeAction;
use App\Http\Action\V1\Data\GetLanguagesAction;
use App\Http\Action\V1\Data\GetSpaceByCityIdAction;
use App\Http\Action\V1\Data\GetSpaceByLocationAction;
use App\Http\Action\V1\Data\GetSpacesAction;
use App\Http\Action\V1\Data\SearchAddressAction;
use App\Http\Action\V1\Identity\GetSignupMethodsAction;
use App\Http\Action\V1\Identity\GetSignupPhotoServerAction;
use App\Http\Action\V1\Identity\GetSystemAction;
use App\Http\Action\V1\Identity\Location\UpdateLocationAction;
use App\Http\Action\V1\Identity\OnlineAction;
use App\Http\Action\V1\Identity\Profile\DeleteProfileAction;
use App\Http\Action\V1\Identity\Profile\GetProfileAction;
use App\Http\Action\V1\Identity\Profile\ProfileGetPhotoServerAction;
use App\Http\Action\V1\Identity\Profile\ProfileSavePhotoAction;
use App\Http\Action\V1\Identity\Profile\UpdateProfileMainAction;
use App\Http\Action\V1\Identity\Profile\UpdateProfilePersonalAction;
use App\Http\Action\V1\Identity\Profile\UpdateProfileStatusAction;
use App\Http\Action\V1\Identity\ReSendAction;
use App\Http\Action\V1\Identity\Restore\RestoreByEmailAction;
use App\Http\Action\V1\Identity\Restore\RestoreByPhoneAction;
use App\Http\Action\V1\Identity\Restore\RestoreCodeAction;
use App\Http\Action\V1\Identity\Restore\RestoreCodeConfirmAction;
use App\Http\Action\V1\Identity\Restore\RestoreSetPasswordAction;
use App\Http\Action\V1\Identity\SignupByEmail\ConfirmAction;
use App\Http\Action\V1\Identity\SignupByEmail\RequestAction;
use App\Http\Action\V1\Identity\SignupByPhone\CaptchaAction;
use App\Http\Action\V1\Identity\Space\UpdateSpaceAction;
use App\Http\Action\V1\Identity\Token\TokenAction;
use App\Http\Action\V1\Identity\Token\TokenDeleteAction;
use App\Http\Action\V1\OpenApiAction;
use App\Http\Action\V1\Posts\Blacklist\HideAction;
use App\Http\Action\V1\Posts\Blacklist\UnHideAction;
use App\Http\Action\V1\Posts\CloseCommentsAction;
use App\Http\Action\V1\Posts\Comments\GetByPostIdAction;
use App\Http\Action\V1\Posts\CreateAction;
use App\Http\Action\V1\Posts\DeleteAction;
use App\Http\Action\V1\Posts\GetByHashtagAction;
use App\Http\Action\V1\Posts\GetPhotoServerAction;
use App\Http\Action\V1\Posts\Like\AddLikeAction;
use App\Http\Action\V1\Posts\Like\DeleteLikeAction;
use App\Http\Action\V1\Posts\Pin\PinAction;
use App\Http\Action\V1\Posts\Pin\UnPinAction;
use App\Http\Action\V1\Posts\PublishAction;
use App\Http\Action\V1\Posts\RepostAction;
use App\Http\Action\V1\Posts\RestoreAction;
use App\Http\Action\V1\Posts\SavePhotoAction;
use App\Http\Action\V1\Posts\ShareAction;
use App\Http\Action\V1\Posts\Union\UnionGetPostponedAction;
use App\Http\Action\V1\Posts\Union\UnionGetPostsAction;
use App\Http\Action\V1\Posts\UpdateAction;
use App\Http\Action\V1\Posts\User\UserGetLikedAction;
use App\Http\Action\V1\Posts\User\UserGetPostponedAction;
use App\Http\Action\V1\Posts\User\UserGetPostsAction;
use App\Http\Action\V1\System\SetExcludeSectionsAction;
use App\Http\Action\V1\Unions\Communities\CreateMusicalAction;
use App\Http\Action\V1\Unions\Communities\GetArtistsNewAction;
use App\Http\Action\V1\Unions\Communities\GetCategoriesBySphereAction;
use App\Http\Action\V1\Unions\Communities\GetCategoriesBySphereWithUnionsAction;
use App\Http\Action\V1\Unions\Communities\GetManageByUserIdAction;
use App\Http\Action\V1\Unions\Communities\GetSpheresAction;
use App\Http\Action\V1\Unions\Communities\GetSpheresWithUnionsAction;
use App\Http\Action\V1\Unions\Communities\UpdateAgeLimitAction;
use App\Http\Action\V1\Unions\Communities\UpdatePrivacyAction;
use App\Http\Action\V1\Unions\Communities\UpdateSectionsAction;
use App\Http\Action\V1\Unions\Events\GetCompletedByUnionIdAction;
use App\Http\Action\V1\Unions\Events\UpdateDatesAction;
use App\Http\Action\V1\Unions\Events\UpdatePlaceAction;
use App\Http\Action\V1\Unions\GetByPermissionsAction;
use App\Http\Action\V1\Unions\JoinAction;
use App\Http\Action\V1\Unions\LeaveAction;
use App\Http\Action\V1\Unions\Members\Contact\GetMembersContactAction;
use App\Http\Action\V1\Unions\Members\GetMembersAction;
use App\Http\Action\V1\Unions\Members\Manage\GetMembersManageAction;
use App\Http\Action\V1\Unions\Places\UpdateAddressAction;
use App\Http\Action\V1\Unions\Places\UpdateWorkingHoursAction;
use App\Http\Action\V1\Users\Blacklist\AddToBlacklistAction;
use App\Http\Action\V1\Users\Blacklist\RemoveFromBlacklistAction;
use App\Http\Action\V1\Users\GetByIdAction;
use App\Http\Action\V1\Users\GetByIdsAction;
use App\Http\Action\V1\Users\GetQRCodeAction;
use App\Http\Action\V1\Users\GetTotalCountAction;
use App\Http\Action\V1\Users\Notification\NotificationSubscribeAction;
use App\Http\Action\V1\Users\Notification\NotificationUnsubscribeAction;
use App\Http\Action\V1\Users\SearchAction;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use ZayMedia\Shared\Components\Router\StaticRouteGroup as Group;

return static function (App $app): void {
    $app->group('/v1', new Group(static function (RouteCollectorProxy $group): void {
        $group->get('', OpenApiAction::class);
        $group->map(['GET', 'POST'], '/auth', AuthAction::class);

        $group->group('/systems', new Group(static function (RouteCollectorProxy $group): void {
            $group->post('/exclude-sections', SetExcludeSectionsAction::class);
        }));

        $group->group('/data', new Group(static function (RouteCollectorProxy $group): void {
            $group->get('/address', SearchAddressAction::class);
            $group->get('/spaces', GetSpacesAction::class);
            $group->get('/spaces/location', GetSpaceByLocationAction::class);
            $group->get('/spaces/cities', GetCitiesWithSpacesAction::class);
            $group->get('/spaces/city/{cityId}', GetSpaceByCityIdAction::class);
            $group->get('/cities', GetCitiesAction::class);
            $group->get('/cities/ids', GetCitiesAction::class);
            $group->get('/languages', GetLanguagesAction::class);
            $group->get('/languages/{code}', GetLanguageByCodeAction::class);
            $group->get('/countries', GetCountriesAction::class);
        }));

        $group->group('/identity', new Group(static function (RouteCollectorProxy $group): void {
            $group->post('/token', TokenAction::class);
            $group->delete('/token', TokenDeleteAction::class);
            $group->put('/location', UpdateLocationAction::class);
            $group->put('/space', UpdateSpaceAction::class);
            $group->get('/profile', GetProfileAction::class);
            $group->delete('/profile', DeleteProfileAction::class);
            $group->patch('/profile/main', UpdateProfileMainAction::class);
            $group->put('/profile/personal', UpdateProfilePersonalAction::class);
            $group->put('/profile/status', UpdateProfileStatusAction::class);
            $group->get('/system', GetSystemAction::class);
            $group->get('/profile/photo-server', ProfileGetPhotoServerAction::class);
            $group->post('/profile/photo', ProfileSavePhotoAction::class);
            $group->post('/online', OnlineAction::class);

            $group->get('/blacklists', Action\V1\Users\Blacklist\GetAction::class);
            $group->post('/blacklists', AddToBlacklistAction::class);
            $group->delete('/blacklists', RemoveFromBlacklistAction::class);

            $group->get('/signup/photo-server', GetSignupPhotoServerAction::class);
            $group->get('/signup/methods', GetSignupMethodsAction::class);
            $group->post('/signup/resend', ReSendAction::class);

            $group->post('/signup/email/request', RequestAction::class);
            $group->post('/signup/email/confirm', ConfirmAction::class);

            $group->post('/signup/phone/captcha', CaptchaAction::class);
            $group->post('/signup/phone/request', Action\V1\Identity\SignupByPhone\RequestAction::class);
            $group->post('/signup/phone/confirm', Action\V1\Identity\SignupByPhone\ConfirmAction::class);

            $group->post('/restore/email', RestoreByEmailAction::class);
            $group->post('/restore/phone', RestoreByPhoneAction::class);
            $group->post('/restore/code', RestoreCodeAction::class);
            $group->post('/restore/code/confirm', RestoreCodeConfirmAction::class);
            $group->post('/restore/password', RestoreSetPasswordAction::class);
        }));

        $group->group('/users', new Group(static function (RouteCollectorProxy $group): void {
            $group->get('', GetByIdsAction::class);
            $group->get('/unions', GetByPermissionsAction::class);
            $group->get('/total', GetTotalCountAction::class);
            $group->get('/{id}/qr-code', GetQRCodeAction::class);
            $group->post('/{id}/notifications', NotificationSubscribeAction::class);
            $group->delete('/{id}/notifications', NotificationUnsubscribeAction::class);
            $group->get('/{id}/subscribers', GetSubscribersAction::class);
            $group->get('/{id}/contacts', GetByUserIdAction::class);
            $group->get('/{id}/contacts/preview', GetByUserIdPreviewAction::class);
            $group->get('/{id}/contacts/online', GetOnlineByUserIdAction::class);
            $group->get('/contacts/birthdays', GetBirthdayAction::class);
            $group->get('/contacts/important', GetImportantAction::class);
            $group->get('/contacts/recommendations', GetRecommendationsAction::class);
            $group->get('/contacts/requests/in', GetRequestsInAction::class);
            $group->get('/contacts/requests/out', GetRequestsOutAction::class);
            $group->post('/contacts/rollback', RollbackAction::class);
            $group->post('/contacts/{id}', AddAction::class);
            $group->delete('/contacts/{id}', RemoveAction::class);
            $group->get('/{id}/mutual/{userId}', GetMutualAction::class);
            $group->get('/{id}/relationship/{userId}', GetRelationshipAction::class);
            $group->get('/search', SearchAction::class);
            $group->get('/search/history', GetSearchHistoryAction::class);
            $group->delete('/search/history', ClearSearchHistoryAction::class);
            $group->post('/import', ImportAction::class);
            $group->get('/{id}', GetByIdAction::class);
            $group->post('/{id}/view', ViewAction::class);

            // Audios albums
            $group->get('/{id}/audio-albums', UserGetAudioAlbumsAction::class);
            $group->post('/{id}/audio-albums/{albumId}', UserAddAudioAlbumAction::class);
            $group->delete('/{id}/audio-albums/{albumId}', UserRemoveAudioAlbumAction::class);

            // Audios playlists
            $group->get('/{id}/audio-playlists', UserGetAudioPlaylistsAction::class);
            $group->post('/{id}/audio-playlists/{playlistId}', UserAddAudioPlaylistAction::class);
            $group->delete('/{id}/audio-playlists/{playlistId}', UserRemoveAudioPlaylistAction::class);

            // Audios
            $group->get('/{id}/audios', UserGetAudiosAction::class);
            $group->get('/{id}/audios/history', GetHistoryAction::class);
            $group->post('/{id}/audios/{audioId}', UserAddAudioAction::class);
            $group->delete('/{id}/audios/{audioId}', UserRemoveAudioAction::class);

            // Posts
            $group->get('/{id}/posts', UserGetPostsAction::class);
            $group->get('/{id}/postponed', UserGetPostponedAction::class);
            $group->get('/{id}/posts/liked', UserGetLikedAction::class);

            // Communities
            $group->get('/{id}/communities', Action\V1\Unions\Communities\GetByUserIdAction::class);
            $group->get('/{id}/communities/manage', GetManageByUserIdAction::class);

            // Events
            $group->get('/{id}/events', Action\V1\Unions\Events\GetByUserIdAction::class);
            $group->get('/{id}/events/manage', Action\V1\Unions\Events\GetManageByUserIdAction::class);

            // Places
            $group->get('/{id}/places', Action\V1\Unions\Places\GetByUserIdAction::class);
            $group->get('/{id}/places/manage', Action\V1\Unions\Places\GetManageByUserIdAction::class);
        }));

        $group->group('/posts', new Group(static function (RouteCollectorProxy $group): void {
            $group->get('/feed', Action\V1\Posts\Feed\GetFeedAction::class);
            $group->get('/photo-server', GetPhotoServerAction::class);
            $group->post('/photos', SavePhotoAction::class);
            $group->get('/hashtag', GetByHashtagAction::class);
            $group->get('/{id}', Action\V1\Posts\GetByIdAction::class);
            $group->put('/{id}', UpdateAction::class);
            $group->patch('/{id}', CloseCommentsAction::class);
            $group->post('/{id}/restore', RestoreAction::class);
            $group->post('/{id}/publish', PublishAction::class);
            $group->post('', CreateAction::class);
            $group->delete('/{id}', DeleteAction::class);
            $group->get('/{id}/share', ShareAction::class);
            $group->post('/{id}/repost', RepostAction::class);
            $group->post('/{id}/pin', PinAction::class);
            $group->delete('/{id}/pin', UnPinAction::class);
            $group->post('/{id}/view', Action\V1\Posts\ViewAction::class);
            $group->post('/{id}/blacklist', HideAction::class);
            $group->delete('/{id}/blacklist', UnHideAction::class);
            $group->post('/{id}/likes', AddLikeAction::class);
            $group->delete('/{id}/likes', DeleteLikeAction::class);

            // Комментарии
            $group->get('/{id}/comments', GetByPostIdAction::class);
            $group->post('/{id}/comments', Action\V1\Posts\Comments\CreateAction::class);
            $group->post('/{id}/comments/photos', Action\V1\Posts\Comments\SavePhotoAction::class);
            $group->get('/{id}/comments/photo-server', Action\V1\Posts\Comments\GetPhotoServerAction::class);
            $group->put('/{id}/comments/{commentId}', Action\V1\Posts\Comments\UpdateAction::class);
            $group->get('/{id}/comments/{commentId}', Action\V1\Posts\Comments\GetByIdAction::class);
            $group->post('/{id}/comments/{commentId}/restore', Action\V1\Posts\Comments\RestoreAction::class);
            $group->delete('/{id}/comments/{commentId}', Action\V1\Posts\Comments\DeleteAction::class);
            $group->post('/{id}/comments/{commentId}/likes', Action\V1\Posts\Comments\Like\AddLikeAction::class);
            $group->delete('/{id}/comments/{commentId}/likes', Action\V1\Posts\Comments\Like\DeleteLikeAction::class);
        }));

        $group->group('/audios', new Group(static function (RouteCollectorProxy $group): void {
            $group->get('', Action\V1\Audios\Audios\SearchAction::class);
            $group->get('/banners', GetBannersAction::class);
            $group->get('/popular', GetPopularAction::class);
            $group->get('/{id}', Action\V1\Audios\Audios\GetByIdAction::class);
            $group->get('/{id}/lyrics', GetLyricsAction::class);
            $group->post('/{id}/listen', ListenAction::class);
            $group->post('/hls', Action\V1\Audios\Audios\SaveHlsInfoAction::class);
        }));

        $group->group('/audio-albums', new Group(static function (RouteCollectorProxy $group): void {
            $group->get('', Action\V1\Audios\AudioAlbums\SearchAction::class);
            $group->get('/new', NewAction::class);
            $group->get('/{id}', Action\V1\Audios\AudioAlbums\GetByIdAction::class);
            $group->get('/{id}/audios', GetAudiosAction::class);
        }));

        $group->group('/audio-playlists', new Group(static function (RouteCollectorProxy $group): void {
            $group->get('', Action\V1\Audios\AudioPlaylists\SearchAction::class);
            $group->get('/new', Action\V1\Audios\AudioPlaylists\NewAction::class);
            $group->get('/{id}', Action\V1\Audios\AudioPlaylists\GetByIdAction::class);
            $group->get('/{id}/audios', Action\V1\Audios\AudioPlaylists\GetAudiosAction::class);
        }));

        $group->group('/unions', new Group(static function (RouteCollectorProxy $group): void {
            $group->get('', Action\V1\Unions\GetByIdsAction::class);
            $group->get('/photo-server', Action\V1\Unions\GetPhotoServerAction::class);
            $group->get('/{id}', Action\V1\Unions\GetByIdAction::class);
            $group->post('/{id}/photo', Action\V1\Unions\SavePhotoAction::class);

            // Member status
            $group->post('/{id}/join', JoinAction::class);
            $group->post('/{id}/leave', LeaveAction::class);
            $group->post('/{id}/invite/{userId}', Action\V1\Unions\InviteAction::class);

            // Notifications
            $group->post('/{id}/notifications', Action\V1\Unions\Notification\NotificationSubscribeAction::class);
            $group->delete('/{id}/notifications', Action\V1\Unions\Notification\NotificationUnsubscribeAction::class);

            // Audios albums
            $group->get('/{id}/audio-albums', UnionGetAudioAlbumsAction::class);

            // Audios playlists
            $group->get('/{id}/audio-playlists', UnionGetAudioPlaylistsAction::class);

            // Audios
            $group->get('/{id}/audios', UnionGetAudiosAction::class);

            // Posts
            $group->get('/{id}/posts', UnionGetPostsAction::class);
            $group->get('/{id}/postponed', UnionGetPostponedAction::class);
            $group->get('/{id}/qr-code', Action\V1\Unions\GetQRCodeAction::class);

            // Members
            $group->get('/{id}/members', GetMembersAction::class);
            $group->get('/{id}/members/manage', GetMembersManageAction::class);
            $group->get('/{id}/members/contact', GetMembersContactAction::class);

            // Contacts
            $group->get('/{id}/contacts', Action\V1\Unions\Contacts\GetAction::class);
            $group->post('/{id}/contacts', Action\V1\Unions\Contacts\CreateAction::class);
            $group->put('/{id}/contacts/{contactId}', Action\V1\Unions\Contacts\UpdateAction::class);
            $group->delete('/{id}/contacts/{contactId}', Action\V1\Unions\Contacts\DeleteAction::class);

            // Links
            $group->get('/{id}/links', Action\V1\Unions\Links\GetAction::class);
            $group->post('/{id}/links', Action\V1\Unions\Links\CreateAction::class);
            $group->put('/{id}/links/{linkId}', Action\V1\Unions\Links\UpdateAction::class);
            $group->delete('/{id}/links/{linkId}', Action\V1\Unions\Links\DeleteAction::class);
        }));

        $group->group('/communities', new Group(static function (RouteCollectorProxy $group): void {
            $group->get('', Action\V1\Unions\Communities\SearchAction::class);
            $group->post('', Action\V1\Unions\Communities\CreateAction::class);
            $group->post('/musical', CreateMusicalAction::class);
            $group->get('/banners', Action\V1\Unions\Communities\GetBannersAction::class);
            $group->get('/recommendations', Action\V1\Unions\Communities\GetRecommendationsAction::class);
            $group->get('/artists/new', GetArtistsNewAction::class);
            $group->get('/spheres', GetSpheresAction::class);
            $group->get('/spheres-unions', GetSpheresWithUnionsAction::class);
            $group->get('/categories', Action\V1\Unions\Communities\GetCategoriesAction::class);
            $group->put('/{id}', Action\V1\Unions\Communities\UpdateAction::class);
            $group->put('/{id}/sections', UpdateSectionsAction::class);
            $group->put('/{id}/age-limit', UpdateAgeLimitAction::class);
            $group->put('/{id}/privacy', UpdatePrivacyAction::class);
            $group->get('/spheres/{id}/categories', GetCategoriesBySphereAction::class);
            $group->get('/spheres/{id}/categories-unions', GetCategoriesBySphereWithUnionsAction::class);
        }));

        $group->group('/events', new Group(static function (RouteCollectorProxy $group): void {
            $group->get('', Action\V1\Unions\Events\SearchAction::class);
            $group->post('', Action\V1\Unions\Events\CreateAction::class);
            $group->get('/feed', Action\V1\Unions\Events\GetFeedAction::class);
            $group->get('/banners', Action\V1\Unions\Events\GetBannersAction::class);
            $group->get('/recommendations', Action\V1\Unions\Events\GetRecommendationsAction::class);
            $group->get('/categories', Action\V1\Unions\Events\GetCategoriesAction::class);
            $group->put('/{id}', Action\V1\Unions\Events\UpdateAction::class);
            $group->put('/{id}/sections', Action\V1\Unions\Events\UpdateSectionsAction::class);
            $group->put('/{id}/age-limit', Action\V1\Unions\Events\UpdateAgeLimitAction::class);
            $group->put('/{id}/place', UpdatePlaceAction::class);
            $group->put('/{id}/dates', UpdateDatesAction::class);
        }));

        $group->group('/places', new Group(static function (RouteCollectorProxy $group): void {
            $group->get('', Action\V1\Unions\Places\SearchAction::class);
            $group->post('', Action\V1\Unions\Places\CreateAction::class);
            $group->get('/banners', Action\V1\Unions\Places\GetBannersAction::class);
            $group->get('/recommendations', Action\V1\Unions\Places\GetRecommendationsAction::class);
            $group->get('/spheres', Action\V1\Unions\Places\GetSpheresAction::class);
            $group->get('/spheres-unions', Action\V1\Unions\Places\GetSpheresWithUnionsAction::class);
            $group->get('/categories', Action\V1\Unions\Places\GetCategoriesAction::class);
            $group->put('/{id}', Action\V1\Unions\Places\UpdateAction::class);
            $group->put('/{id}/address', UpdateAddressAction::class);
            $group->put('/{id}/sections', Action\V1\Unions\Places\UpdateSectionsAction::class);
            $group->put('/{id}/age-limit', Action\V1\Unions\Places\UpdateAgeLimitAction::class);
            $group->put('/{id}/working-hours', UpdateWorkingHoursAction::class);
            $group->get('/{id}/events', Action\V1\Unions\Events\GetByUnionIdAction::class);
            $group->get('/{id}/events/completed', GetCompletedByUnionIdAction::class);
            $group->get('/spheres/{id}/categories', Action\V1\Unions\Places\GetCategoriesBySphereAction::class);
            $group->get('/spheres/{id}/categories-unions', Action\V1\Unions\Places\GetCategoriesBySphereWithUnionsAction::class);
        }));
    }));
};

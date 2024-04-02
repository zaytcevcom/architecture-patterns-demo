<?php

declare(strict_types=1);

namespace App\Http\Action\V1;

use OpenApi\Attributes as OA;
use OpenApi\Generator;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use ZayMedia\Shared\Http\Response\JsonResponse;

use function App\Components\env;

#[OA\Info(
    version: '1.0',
    title: 'API'
)]
#[OA\Server(
    url: '/v1/'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    name: 'bearerAuth',
    in: 'header',
    bearerFormat: 'JWT',
    scheme: 'bearer'
)]
#[OA\Tag(
    name: 'OAuth',
    description: 'OAuth'
)]
#[OA\Tag(
    name: 'Identity',
    description: 'ID'
)]
#[OA\Tag(
    name: 'Identity (Socials)',
    description: 'Интеграции'
)]
#[OA\Tag(
    name: 'Identity (Signup)',
    description: 'Регистрация'
)]
#[OA\Tag(
    name: 'Identity (Signup by email)',
    description: 'Регистрация через email'
)]
#[OA\Tag(
    name: 'Identity (Signup by phone)',
    description: 'Регистрация через sms'
)]
#[OA\Tag(
    name: 'Identity (Restore)',
    description: 'Восстановление пароля'
)]
#[OA\Tag(
    name: 'Identity (Blacklists)',
    description: 'Черный список'
)]
#[OA\Tag(
    name: 'Data',
)]
#[OA\Tag(
    name: 'Users',
    description: 'Пользователи'
)]
#[OA\Tag(
    name: 'Users (Contacts)',
    description: 'Пользователи (Раздел "Контакты")'
)]
#[OA\Tag(
    name: 'Users (Contacts relations)',
    description: 'Пользователи — Взаимоотношения'
)]
#[OA\Tag(
    name: 'Users (Contacts Import)',
    description: 'Пользователи (Раздел "Контакты") — Импорт'
)]
#[OA\Tag(
    name: 'Users (Notifications)',
    description: 'Подписка на уведомления от пользователи'
)]
#[OA\Tag(
    name: 'Photos (User)',
    description: 'Фотографии пользователя'
)]
#[OA\Tag(
    name: 'Photo albums (User)',
    description: 'Фотоальбомы пользователя'
)]
#[OA\Tag(
    name: 'Photos (Comments)',
    description: 'Комментарии к фотографиям'
)]
#[OA\Tag(
    name: 'Audios',
    description: 'Аудиозаписи'
)]
#[OA\Tag(
    name: 'Audios albums',
    description: 'Аудио-альбомы'
)]
#[OA\Tag(
    name: 'Audios playlists',
    description: 'Плейлисты'
)]
#[OA\Tag(
    name: 'Audios (User)',
    description: 'Аудиозаписи пользователя'
)]
#[OA\Tag(
    name: 'Audios albums (User)',
    description: 'Аудио-альбомы пользователя'
)]
#[OA\Tag(
    name: 'Audios playlists (User)',
    description: 'Плейлисты пользователя'
)]
#[OA\Tag(
    name: 'Posts',
    description: 'Посты'
)]
#[OA\Tag(
    name: 'Posts (User)',
    description: 'Посты пользователя'
)]
#[OA\Tag(
    name: 'Posts (Comments)',
    description: 'Комментарии к постам'
)]
#[OA\Tag(
    name: 'Videos',
    description: 'Видеозаписи'
)]
#[OA\Tag(
    name: 'Videos (Comments)',
    description: 'Комментарии к видеозаписям'
)]
#[OA\Tag(
    name: 'Calls',
    description: 'Общение — Звонки'
)]
#[OA\Tag(
    name: 'Messenger',
    description: 'Общение — Мессенджер'
)]
#[OA\Tag(
    name: 'Messenger (Messages)',
    description: 'Общение — Сообщения'
)]
#[OA\Tag(
    name: 'Messenger (Management)',
    description: 'Общение — Управление чатами'
)]
#[OA\Tag(
    name: 'Unions',
    description: 'Объединения'
)]
#[OA\Tag(
    name: 'Unions (Socials)',
    description: 'Интеграции'
)]
#[OA\Tag(
    name: 'Unions (Management)',
    description: 'Объединения — Управление'
)]
final class OpenApiAction implements RequestHandlerInterface
{
    public function handle(Request $request): Response
    {
        $openapi = Generator::scan([env('OPENAPI_PATH')]);

        return new JsonResponse($openapi);
    }
}

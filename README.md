<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>



---

# FeastBoom

FeastBoom — справочник по банкетным залам: каталог с фотографиями, видео, информацией о ведущих и многом другом. Приложение состоит из Android-клиента (FeastBoom) и серверного API на Laravel.

## Стек проекта

### Мобильное приложение (Android)

- **Kotlin** + **Jetpack** (Jetpack Compose / ViewModel / Lifecycle)
- **Без DI-фреймворков** — зависимости собираются вручную через `by lazy` в Activity/Application
- **Retrofit** — единый клиент, собирается в `NetworkModule`
- **AuthStorage** на обычных `SharedPreferences` (без EncryptedSharedPreferences)
- **Firebase** — push-уведомления (FCM)

### Бэкенд

- **Laravel 10** + **PHP 8.1**
- **Laravel Sanctum** — аутентификация

## API

| Интерфейс | Назначение |
|---|---|
| `ApiService` | чаты, сообщения, FCM |
| `AuthApi` | только логин |

Оба интерфейса работают через единый Retrofit-клиент из `NetworkModule`, зависимости подключаются через `by lazy` — без создания новых интерфейсов и без Dagger/Hilt/KSP.

**Контракты и соглашения:**

- `userId` передаётся как `Int?`
- В ответах бэкенд отдаёт только то, что нужно клиенту: например, для статуса непрочитанных — булево значение, без лишних полей
- Логирование — строго через два канала:
    - `Log::channel('info_file')->info([]);`
    - `Log::channel('error_file')->error([]);`

## Структура проекта

Структуру соблюдать строго:

```
app/
├── Http/
│   ├── Controllers/Api/    — контроллеры API (авторизация, чаты, сообщения, FCM-токены)
│   ├── Requests/           — FormRequest-классы для валидации входных данных
│   └── Resources/          — трансформация ответов (отдаём только нужное клиенту)
├── Models/                 — Eloquent-модели
├── Repositories/           — работа с данными
└── Services/               — бизнес-логика

routes/
└── api.php                 — маршруты API (Sanctum)
└── web.php

database/
├── migrations/             — миграции
└── seeders/                — сиды

config/
└── logging.php             — каналы info_file и error_file
```



# IT Service Desk

Курсовой проект: веб-приложение для создания и обработки заявок технической поддержки.
Стандартная архитектура Laravel MVC, серверный интерфейс на Blade и Bootstrap, административная панель Backpack.

## Возможности

- Регистрация, вход по email и паролю, запоминание входа, выход через POST.
- Роли `user` и `admin`. Регистрация всегда создаёт обычного пользователя.
- Профиль: имя, email, смена пароля с проверкой текущего, загрузка JPG/PNG/WebP до 2 МБ.
- При замене аватара старый файл удаляется после успешного сохранения нового пути в БД.
- Личный Dashboard: общее число заявок, счётчики по статусам, последние обращения.
- Создание, список с пагинацией, просмотр, редактирование и мягкое удаление собственных заявок.
- Проверка владельца через `TicketPolicy` на сервере, включая прямые обращения к URL.
- Backpack: просмотр пользователей и их ролей; все заявки, редактирование, статус, приоритет и Soft Delete; полный CRUD категорий.
- Категории, используемые в заявках, включая мягко удалённые, защищены от удаления.
- Русский интерфейс, адаптивная навигация, таблицы, формы и badges, страницы ошибок.
- Form Requests, CSRF, ограничение частоты входа, хеширование паролей, защита от Mass Assignment.
- Стандартные логи Laravel. Изменения заявок администратором записываются в журнал без содержимого заявок и паролей.

## Стек и требования

Проверенные версии: PHP **8.3.33**, Laravel **13.33.0**, Backpack CRUD **7.1.20**, Theme Tabler **2.1.0**, Bootstrap **5.3.8**, MariaDB **11.4.8**, Composer **2.10.3**.
Точные версии PHP-пакетов закреплены в `composer.lock`.

- PHP 8.3+ и Composer 2.
- MySQL 8+ или MariaDB 10.11+; практическая проверка выполнена на MariaDB 11.4.8.
- Расширения PHP: ctype, curl, dom, fileinfo, filter, hash, mbstring, openssl, PDO, **pdo_mysql**, session, tokenizer, xml, xmlwriter, zip. **pdo_sqlite** — для тестов в памяти, **gd** — для тестов аватаров.
- Доступ на запись к `storage/` и `bootstrap/cache/`.
- Интернет требуется при установке Composer-пакетов и первоначальном кешировании ресурсов Backpack.
- Node.js, npm, Docker, Redis, очереди и сборка frontend для запуска не нужны. Bootstrap включён в `public/`.

Официальные справочники: [Laravel 13](https://laravel.com/docs/13.x), [Backpack 7](https://backpackforlaravel.com/docs/7.x/installation).
Используются бесплатные CRUD-компоненты, без Backpack PRO.

## Установка после клонирования

```powershell
cd it-service-desk
composer install
Copy-Item .env.example .env
php artisan key:generate
```

В Linux/macOS вместо `Copy-Item` используйте `cp .env.example .env`.
Команда копирования предназначена для новой установки; не заменяйте уже настроенный `.env`.

Если PHP сообщает `could not find driver`, включите `extension=pdo_mysql` в активном `php.ini` (`php --ini`).
Для проверки расширений используйте `php -m`.

### Создание базы

Войдите в MySQL/MariaDB под учётной записью, имеющей право создавать базы и пользователей:

```sql
CREATE DATABASE it_service_desk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'it_service_desk'@'127.0.0.1' IDENTIFIED BY 'replace-with-your-local-password';
GRANT ALL PRIVILEGES ON it_service_desk.* TO 'it_service_desk'@'127.0.0.1';
```

Настройте `.env` под свой сервер:

```dotenv
APP_NAME="IT Service Desk"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
APP_LOCALE=ru
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=it_service_desk
DB_USERNAME=it_service_desk
DB_PASSWORD=replace-with-your-local-password
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

Не публикуйте `.env`. В `.env.example` нет реальных ключей и паролей.
Перед размещением на публичном сервере задайте `APP_ENV=production`, `APP_DEBUG=false`, HTTPS, `SESSION_SECURE_COOKIE=true` и замените демонстрационные учётные записи. Корень веб-сервера должен указывать на `public/`.

### Миграции, ресурсы и запуск

```powershell
php artisan migrate --seed
php artisan storage:link
php artisan basset:cache
php artisan serve --host=127.0.0.1 --port=8000
```

`storage:link` создаёт ссылку для аватаров и ресурсов Backpack. Если Windows запрещает создание ссылки, разрешите создание ссылок для своей среды или выполните эту команду из терминала с нужными правами.
`basset:cache` загружает и сохраняет ресурсы административной панели. При ошибке TLS настройте доверенный CA bundle в `curl.cainfo` и `openssl.cafile`; проверку сертификатов отключать не нужно.
Вход в административную панель выполняется через общую форму `/login`.

Завершить `php artisan serve` можно сочетанием Ctrl+C в его терминале. База данных управляется отдельно.

## Демонстрационные данные

| Роль | Email | Пароль |
|---|---|---|
| Администратор | `admin@example.test` | `Admin12345!` |
| Пользователь 1 | `user1@example.test` | `User12345!` |
| Пользователь 2 | `user2@example.test` | `User12345!` |
| Пользователь 3 | `user3@example.test` | `User12345!` |

Сидеры создают 5 категорий и 18 заявок (по 6 у каждого обычного пользователя), с разными статусами и приоритетами. Используются вымышленные данные и домен `.test`.
Повторный `php artisan db:seed` не дублирует демонстрационные записи и не сбрасывает существующие пароли.

Для полного пересоздания **только учебной базы**, с удалением всех её текущих данных:

```powershell
php artisan migrate:fresh --seed
```

## Роли и правила доступа

| Действие | user | admin |
|---|---|---|
| Личный кабинет и профиль | Да | Да |
| CRUD собственных заявок | Да | Да |
| Чужие заявки через пользовательские URL | Нет, 403 | Нет; управление через Backpack |
| Все заявки и изменение статуса в Backpack | Нет, 403 | Да |
| CRUD категорий | Нет | Да |
| Список и карточки пользователей | Нет | Да, только чтение |

При создании: `status=new`, `priority=medium`, автор берётся из текущей сессии.
Статусы: `new`, `in_progress`, `resolved`, `closed`. Приоритеты: `low`, `medium`, `high`, `critical`.
Обычный пользователь не может передать `status` или `user_id` через форму/API: Form Request отклонит такой запрос.
Поле `role` не входит в разрешённые поля массового заполнения `User`.
Удалённые заявки недоступны через обычные маршруты и исключаются из статистики. Восстановление и отдельный интерфейс корзины в MVP не предусмотрены.

Backpack использует guard `web`, middleware `EnsureAdmin` и стандартный `Illuminate\Session\Middleware\AuthenticateSession` Laravel 13. Это обеспечивает общую сессию входа и совместимость с актуальным форматом хеша сессии.
Регистрация через Backpack, восстановление пароля по почте и изменение ролей из браузера отключены, поскольку не входят в требования.

## Тестирование

```powershell
php artisan test
php scripts/check-syntax.php
composer validate --strict
php artisan optimize:clear
php artisan migrate:status
php artisan route:list
```

На подготовленном компьютере вместо `php` используйте `.\scripts\php.ps1`, чтобы применять локальный PHP-конфиг.
По умолчанию PHPUnit использует SQLite `:memory:`, отдельную сессию и кеш в памяти; учебная MariaDB при этом не затрагивается.

Для тестирования на MySQL/MariaDB создайте **отдельную** базу:

```sql
CREATE DATABASE it_service_desk_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON it_service_desk_testing.* TO 'it_service_desk'@'127.0.0.1';
```

```powershell
.\scripts\test-mysql.ps1
```

Скрипт использует данные подключения из `.env`, заменяя только имя БД и тип соединения. Принимает только имя, заканчивающееся на `_testing`. PHPUnit пересоздаёт таблицы в этой базе.

Покрыты регистрация и хеширование, вход/выход и throttling, гостевой доступ, CRUD заявок, ограничения владельца, Soft Delete, значения по умолчанию, валидация, XSS escaping, профиль и удаление старого аватара, пароль, роль администратора, настоящая сессия входа в Backpack, список всех заявок, административные изменения, CRUD и ограничения категорий, повторный запуск сидеров.
Результаты фактических проверок и список сценариев: [docs/VERIFICATION.md](docs/VERIFICATION.md).

## Структура

```text
app/Models/                 User, Ticket, Category; связи Eloquent и SoftDeletes
app/Policies/               TicketPolicy
app/Http/Controllers/       Auth, Dashboard, Profile, Ticket
app/Http/Controllers/Admin/ Backpack CRUD-контроллеры
app/Http/Requests/          Валидация пользовательских и административных форм
app/Http/Middleware/        EnsureAdmin
database/migrations/       Схема пользователей, ролей, категорий и заявок
database/seeders/           Демонстрационные записи
database/factories/         UserFactory, TicketFactory, CategoryFactory
routes/web.php             Пользовательские маршруты
routes/backpack/custom.php  Административные маршруты
resources/views/           Blade-страницы и изменения шаблонов Backpack
public/css, public/js/     Bootstrap и минимальные стили/скрипты приложения
lang/ru/                   Переводы ошибок валидации и пагинации
tests/Feature/             Проверки HTTP, базы данных и прав доступа
scripts/                   Проверка синтаксиса и команды для Windows
```

Основные таблицы: `users` (включая `role`, `avatar`), `categories`, `tickets`.
Стандартные инфраструктурные таблицы Laravel: `migrations`, `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`. Worker очереди не используется; приложение работает с `QUEUE_CONNECTION=sync`, файловыми сессиями и кешем.
Связи: User → hasMany Tickets; Category → hasMany Tickets; Ticket → belongsTo User и Category. Внешние ключи сохраняют целостность записей.

Все даты в приложении хранятся и выводятся в UTC, согласно стандартной настройке `config/app.php`.
Логи: `storage/logs/laravel.log`.

## Ручной сценарий демонстрации

1. Зарегистрировать пользователя, выйти и войти снова.
2. На Dashboard создать заявку, открыть её, изменить и удалить; проверить список и счётчики.
3. Изменить профиль, загрузить новый аватар, повторить загрузку, сменить пароль.
4. Войти под `admin@example.test`, открыть «Админ-панель».
5. Просмотреть пользователей и все заявки, изменить статус/приоритет заявки.
6. Создать, открыть, изменить и удалить новую свободную категорию. Удаление используемой категории должно быть отклонено.
7. Войти обычным пользователем и проверить запрет `/admin` и доступа к чужой заявке.

## Git

Репозиторий проекта: https://github.com/Sergey2313-arch/-PHP, основная ветка — `main`.
`.gitignore` исключает `.env`, локальную базу, служебные секреты, `vendor`, `node_modules`, кеши, логи и загруженные файлы.
В репозитории хранятся исходники, миграции, seeders, тесты, `composer.lock`, `.env.example`, документация и скриншоты.
Зависимости устанавливаются командой `composer install`, а демонстрационные данные создаются через `php artisan migrate --seed`.

# BSTU Presentation

PHP-приложение для презентационного сайта БГТУ им. В.Г. Шухова с разделами:

- почётные доктора;
- почётные профессора;
- простая админка;
- импорт из Word-файлов с фото и биографиями.

## Стек

- PHP 8.2+;
- PDO;
- MySQL для продакшна;
- SQLite как локальный fallback для разработки, если MySQL не поднят.

## Быстрый старт

1. Скопируйте `.env.example` в `.env`.
2. Настройте подключение к БД.
3. Примените схему:

```bash
php scripts/migrate.php
```

4. Импортируйте исходные данные:

```bash
php scripts/import_honors.php
```

5. Запустите локальный сервер:

```bash
php -S 127.0.0.1:8000 -t public
```

6. Откройте:

- сайт: `http://127.0.0.1:8000`
- админка: `http://127.0.0.1:8000/admin/login.php`

## Админка

Логин и пароль берутся из `.env`:

- `ADMIN_USERNAME`
- `ADMIN_PASSWORD`

Если хотите хранить не открытый пароль, можно передать bcrypt-хэш в `ADMIN_PASSWORD`.

## Контуры

- `presentation.bstu.ru` — основной контур;
- `demo.devfit.ru` — тестовый контур для согласования правок;
- у demo и prod должны быть отдельные `.env`, отдельные учётные данные админки и отдельные данные БД;
- для demo стоит включать `APP_ENV=demo`, для локальной разработки `APP_ENV=local`.

## Выкладка

Для ручной выкладки на сервер есть скрипт:

```bash
BSTU_SERVER_PASSWORD='***' ./deploy/deploy_contour.sh demo
BSTU_SERVER_PASSWORD='***' ./deploy/deploy_contour.sh prod
```

Скрипт:

- выкладывает код только в выбранный контур;
- не перезаписывает `.env`;
- не трогает `storage/database.sqlite`;
- не трогает runtime-загрузки из `public/uploads/manual` и `public/uploads/presentations`.

<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Database;
use App\Repositories\PersonRepository;
use App\Support\Env;

require_once __DIR__ . '/Support/helpers.php';
require_once __DIR__ . '/Support/Env.php';
require_once __DIR__ . '/Support/PresentationCatalog.php';
require_once __DIR__ . '/Support/Rectors.php';
require_once __DIR__ . '/Core/Database.php';
require_once __DIR__ . '/Core/Auth.php';
require_once __DIR__ . '/Repositories/PersonRepository.php';
require_once __DIR__ . '/Repositories/PresentationRepository.php';
require_once __DIR__ . '/Support/WordImporter.php';

Env::load(base_path('.env'));

$appConfig = [
    'app' => [
        'name' => $_ENV['APP_NAME'] ?? 'Презентация БГТУ им. В.Г. Шухова',
        'env' => $_ENV['APP_ENV'] ?? '',
        'url' => $_ENV['APP_URL'] ?? 'http://127.0.0.1:8000',
        'timezone' => $_ENV['APP_TIMEZONE'] ?? 'Europe/Moscow',
    ],
    'db' => [
        'driver' => $_ENV['DB_DRIVER'] ?? 'sqlite',
        'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'port' => $_ENV['DB_PORT'] ?? '3306',
        'database' => $_ENV['DB_DATABASE'] ?? storage_path('database.sqlite'),
        'username' => $_ENV['DB_USERNAME'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
    ],
    'auth' => [
        'username' => $_ENV['ADMIN_USERNAME'] ?? 'admin',
        'password' => $_ENV['ADMIN_PASSWORD'] ?? 'admin123',
    ],
];

if (($appConfig['db']['driver'] ?? 'sqlite') === 'sqlite') {
    $databasePath = (string) $appConfig['db']['database'];

    if ($databasePath !== '' && !str_starts_with($databasePath, DIRECTORY_SEPARATOR)) {
        $appConfig['db']['database'] = base_path($databasePath);
    }
}

date_default_timezone_set(config('app.timezone', 'Europe/Moscow'));

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('bstu_presentation');
    session_start();
}

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $pdo = Database::connect(config('db'));

    return $pdo;
}

function person_repository(): PersonRepository
{
    static $repository = null;

    if ($repository instanceof PersonRepository) {
        return $repository;
    }

    $repository = new PersonRepository(db());

    return $repository;
}

function presentation_repository(): \App\Repositories\PresentationRepository
{
    static $repository = null;

    if ($repository instanceof \App\Repositories\PresentationRepository) {
        return $repository;
    }

    $repository = new \App\Repositories\PresentationRepository(db());

    return $repository;
}

function auth(): Auth
{
    static $auth = null;

    if ($auth instanceof Auth) {
        return $auth;
    }

    $auth = new Auth(
        (string) config('auth.username'),
        (string) config('auth.password')
    );

    return $auth;
}

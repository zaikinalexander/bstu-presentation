<?php

declare(strict_types=1);

function base_path(string $path = ''): string
{
    $base = dirname(__DIR__, 2);

    return $path === '' ? $base : $base . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
}

function public_path(string $path = ''): string
{
    $base = base_path('public');

    return $path === '' ? $base : $base . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
}

function storage_path(string $path = ''): string
{
    $base = base_path('storage');

    return $path === '' ? $base : $base . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function asset(string $path): string
{
    return '/' . ltrim($path, '/');
}

function redirect(string $location): never
{
    header('Location: ' . $location);
    exit;
}

function request_method(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

function is_post(): bool
{
    return request_method() === 'POST';
}

function flash(string $key, ?string $value = null): ?string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if ($value !== null) {
        $_SESSION['_flash'][$key] = $value;

        return null;
    }

    $message = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);

    return $message;
}

function old(string $key, ?string $default = null): ?string
{
    return $_POST[$key] ?? $default;
}

function config(?string $key = null, mixed $default = null): mixed
{
    global $appConfig;

    if ($key === null) {
        return $appConfig;
    }

    $segments = explode('.', $key);
    $value = $appConfig;

    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }

        $value = $value[$segment];
    }

    return $value;
}

function app_url_host(): string
{
    $host = parse_url((string) config('app.url', ''), PHP_URL_HOST);

    return is_string($host) ? strtolower($host) : '';
}

function app_environment(): string
{
    $environment = strtolower(normalized_whitespace((string) config('app.env', '')));

    if ($environment !== '') {
        return match ($environment) {
            'prod' => 'production',
            default => $environment,
        };
    }

    $host = app_url_host();

    return match (true) {
        $host === 'demo.devfit.ru' => 'demo',
        $host === '127.0.0.1', $host === 'localhost' => 'local',
        str_ends_with($host, '.local'), str_ends_with($host, '.test') => 'local',
        default => 'production',
    };
}

function is_production_environment(): bool
{
    return app_environment() === 'production';
}

function environment_label(): string
{
    return match (app_environment()) {
        'demo' => 'DEMO',
        'local' => 'LOCAL',
        default => 'PROD',
    };
}

function environment_description(): string
{
    return match (app_environment()) {
        'demo' => 'Тестовый контур для согласований. Изменения не попадают на основной сайт автоматически.',
        'local' => 'Локальный контур разработки.',
        default => 'Основной рабочий контур.',
    };
}

function render_environment_banner(): string
{
    if (is_production_environment()) {
        return '';
    }

    $label = e(environment_label());
    $description = e(environment_description());

    return <<<HTML
<div class="environment-banner" role="status" aria-label="Текущий контур">
    <strong class="environment-banner__label">{$label}</strong>
    <span class="environment-banner__text">{$description}</span>
</div>
HTML;
}

function transliterate(string $value): string
{
    $map = [
        'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'E',
        'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M',
        'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U',
        'Ф' => 'F', 'Х' => 'Kh', 'Ц' => 'Ts', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch',
        'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e',
        'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
        'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
        'ф' => 'f', 'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
        'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
    ];

    return strtr($value, $map);
}

function slugify(string $value): string
{
    $value = transliterate($value);
    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    $value = trim($value, '-');

    return $value !== '' ? $value : 'item';
}

function normalized_whitespace(string $value): string
{
    $value = str_replace(["\xc2\xa0", "\r", "\n", "\t"], ' ', $value);
    $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

    return trim($value);
}

function sort_name(string $value): string
{
    $value = normalized_whitespace($value);
    $value = preg_replace('/^(доктор|профессор)\s+/iu', '', $value) ?? $value;

    return $value;
}

function alphabet_letter(string $value): string
{
    $value = sort_name($value);
    $first = mb_strtoupper(mb_substr($value, 0, 1));

    if ($first === 'Ё') {
        return 'Е';
    }

    return $first !== '' ? $first : '#';
}

function excerpt(string $value, int $length = 180): string
{
    $value = normalized_whitespace(strip_tags($value));

    if (mb_strlen($value) <= $length) {
        return $value;
    }

    return rtrim(mb_substr($value, 0, $length - 1)) . '…';
}

function ucfirst_display(string $value): string
{
    $value = normalized_whitespace($value);

    if ($value === '') {
        return '';
    }

    $updated = preg_replace_callback(
        '/^([^\p{L}]*)(\p{L})/u',
        static fn (array $matches): string => $matches[1] . mb_strtoupper($matches[2]),
        $value,
        1
    );

    return $updated ?? $value;
}

function person_headline_parts(string $headline): array
{
    $headline = normalized_whitespace(strip_tags($headline));
    $years = '';

    if (preg_match('/^\((\d{4}\s*[–-]\s*\d{4})\)\s*(?:[–—-]\s*)?(.*)$/u', $headline, $matches)) {
        $years = normalized_whitespace($matches[1]);
        $headline = normalized_whitespace($matches[2] ?? '');
    }

    return [
        'years' => $years,
        'text' => ucfirst_display($headline),
    ];
}

function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(24));
    }

    return $_SESSION['_csrf'];
}

function verify_csrf(?string $token): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    return is_string($token) && hash_equals($_SESSION['_csrf'] ?? '', $token);
}

function type_label(string $type): string
{
    return $type === 'doctor' ? 'Почётные доктора' : 'Почётные профессора';
}

function type_label_singular(string $type): string
{
    return $type === 'doctor' ? 'Почётный доктор' : 'Почётный профессор';
}

function pluralize_ru(int $count, string $one, string $few, string $many): string
{
    $mod100 = $count % 100;
    $mod10 = $count % 10;

    if ($mod100 >= 11 && $mod100 <= 14) {
        return $many;
    }

    if ($mod10 === 1) {
        return $one;
    }

    if ($mod10 >= 2 && $mod10 <= 4) {
        return $few;
    }

    return $many;
}

<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$layout = ($GLOBALS['person_layout'] ?? 'classic') === 'compact' ? 'compact' : 'classic';
$catalogPath = $layout === 'compact' ? '/honors2.php' : '/honors1.php';
$type = ($_GET['type'] ?? 'doctor') === 'professor' ? 'professor' : 'doctor';
$slug = normalized_whitespace((string) ($_GET['slug'] ?? ''));
$person = $slug !== '' ? person_repository()->findBySlug($type, $slug) : null;
$headline = $person !== null ? person_headline_parts((string) ($person['headline'] ?? '')) : ['years' => '', 'text' => ''];

if ($person === null) {
    http_response_code(404);
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($person ? $person['full_name'] . ' | ' . config('app.name') : 'Карточка не найдена') ?></title>
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
</head>
<body class="page-person<?= $layout === 'compact' ? ' page-person--compact' : '' ?>">
<main class="shell person-page">
    <div class="person-page__topbar">
        <a class="button button--ghost" href="<?= e($catalogPath) ?>?type=<?= e($type) ?>">Назад к каталогу</a>
        <a class="button button--ghost" href="/index.php">На главную</a>
    </div>

    <?php if ($person === null): ?>
        <section class="empty-state">
            <h1>Карточка не найдена</h1>
            <p>Запись отсутствует в каталоге или была снята с публикации.</p>
        </section>
    <?php else: ?>
        <?php if ($layout === 'compact'): ?>
            <section class="person-sheet">
                <div class="person-summary">
                    <div class="person-summary__media">
                        <?php if (!empty($person['image_path'])): ?>
                            <img src="<?= e('/' . ltrim($person['image_path'], '/')) ?>" alt="<?= e($person['full_name']) ?>">
                        <?php else: ?>
                            <div class="person-card__placeholder person-card__placeholder--compact-detail"><?= e($person['alphabet_letter']) ?></div>
                        <?php endif; ?>
                    </div>
                <div class="person-summary__content">
                    <div class="person-hero__eyebrow"><?= e(type_label_singular($type)) ?></div>
                    <h1><?= e($person['full_name']) ?></h1>
                    <?php if ($headline['years'] !== ''): ?>
                        <p class="person-hero__years"><?= e($headline['years']) ?></p>
                    <?php endif; ?>
                    <?php if ($headline['text'] !== ''): ?>
                        <p class="person-hero__headline"><?= e($headline['text']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

                <div class="person-content person-content--compact">
                    <?php foreach (preg_split('/\R{2,}/u', trim((string) $person['biography'])) ?: [] as $paragraph): ?>
                        <?php if (trim($paragraph) !== ''): ?>
                            <p><?= nl2br(e(trim($paragraph))) ?></p>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <?php if (!empty($person['award_note'])): ?>
                        <div class="person-fact person-fact--trailing">
                            <span>Статус</span>
                            <strong><?= e($person['award_note']) ?></strong>
                        </div>
                    <?php endif; ?>

                    <div class="person-content__actions">
                        <a class="button button--ghost" href="<?= e($catalogPath) ?>?type=<?= e($type) ?>">Назад к каталогу</a>
                        <a class="button button--ghost" href="/index.php">На главную</a>
                    </div>
                </div>
            </section>
        <?php else: ?>
            <section class="person-hero">
                <div class="person-hero__media">
                    <?php if (!empty($person['image_path'])): ?>
                        <img src="<?= e('/' . ltrim($person['image_path'], '/')) ?>" alt="<?= e($person['full_name']) ?>">
                    <?php else: ?>
                        <div class="person-card__placeholder"><?= e($person['alphabet_letter']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="person-hero__content">
                    <div class="person-hero__eyebrow"><?= e(type_label_singular($type)) ?></div>
                    <h1><?= e($person['full_name']) ?></h1>
                    <?php if ($headline['years'] !== ''): ?>
                        <p class="person-hero__years"><?= e($headline['years']) ?></p>
                    <?php endif; ?>
                    <?php if ($headline['text'] !== ''): ?>
                        <p class="person-hero__headline"><?= e($headline['text']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($person['award_note'])): ?>
                        <div class="person-fact">
                            <span>Статус</span>
                            <strong><?= e($person['award_note']) ?></strong>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
            <section class="person-content">
                <?php foreach (preg_split('/\R{2,}/u', trim((string) $person['biography'])) ?: [] as $paragraph): ?>
                    <?php if (trim($paragraph) !== ''): ?>
                        <p><?= nl2br(e(trim($paragraph))) ?></p>
                    <?php endif; ?>
                <?php endforeach; ?>

                <div class="person-content__actions">
                    <a class="button button--ghost" href="<?= e($catalogPath) ?>?type=<?= e($type) ?>">Назад к каталогу</a>
                    <a class="button button--ghost" href="/index.php">На главную</a>
                </div>
            </section>
        <?php endif; ?>
    <?php endif; ?>
</main>
</body>
</html>

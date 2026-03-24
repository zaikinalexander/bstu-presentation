<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$layout = ($GLOBALS['honors_layout'] ?? 'classic') === 'compact' ? 'compact' : 'classic';
$selfPath = '/' . basename((string) ($_SERVER['SCRIPT_NAME'] ?? 'honors.php'));
$personPath = $layout === 'compact' ? '/person2.php' : '/person1.php';
$type = ($_GET['type'] ?? 'doctor') === 'professor' ? 'professor' : 'doctor';
$search = normalized_whitespace((string) ($_GET['q'] ?? ''));
$letter = normalized_whitespace((string) ($_GET['letter'] ?? ''));

$introText = $type === 'doctor'
    ? 'Ознакомьтесь с почётными докторами университета, воспользуйтесь поиском или выберите нужную букву для быстрого перехода к интересующей персоналии.'
    : 'Ознакомьтесь с почётными профессорами университета, воспользуйтесь поиском или выберите нужную букву для быстрого перехода к интересующей персоналии.';

$searchPlaceholder = $type === 'doctor'
    ? 'Найти почётного доктора'
    : 'Найти почётного профессора';

$emptyTitle = $type === 'doctor'
    ? 'Почётные доктора не найдены'
    : 'Почётные профессора не найдены';

$people = person_repository()->all($type, [
    'search' => $search,
    'letter' => $letter,
]);
$letters = person_repository()->letters($type);
$title = type_label($type) . ' | ' . config('app.name');
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
</head>
<body class="page-catalog<?= $layout === 'compact' ? ' page-catalog--compact' : '' ?>">
<div class="subhero">
    <div class="shell subhero__content">
        <a class="button button--ghost" href="/index.php">На главную</a>
        <div>
            <?php if ($layout !== 'compact'): ?>
                <div class="section__eyebrow">Каталог</div>
            <?php endif; ?>
            <h1><?= e(type_label($type)) ?></h1>
            <?php if ($layout !== 'compact'): ?>
                <p><?= e($introText) ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<main class="shell">
    <section class="toolbar">
        <form class="search-form" method="get">
            <input type="hidden" name="type" value="<?= e($type) ?>">
            <label class="search-form__field">
                <span class="sr-only">Поиск</span>
                <input type="search" name="q" value="<?= e($search) ?>" placeholder="<?= e($searchPlaceholder) ?>">
            </label>
            <?php if ($letter !== ''): ?>
                <input type="hidden" name="letter" value="<?= e($letter) ?>">
            <?php endif; ?>
            <button class="button button--primary" type="submit">Найти</button>
            <?php if ($search !== '' || $letter !== ''): ?>
                <a class="button button--ghost" href="<?= e($selfPath) ?>?type=<?= e($type) ?>">Сбросить</a>
            <?php endif; ?>
        </form>

        <div class="alphabet-nav" data-letter-nav>
            <a class="alphabet-nav__item<?= $letter === '' ? ' is-active' : '' ?>" href="<?= e($selfPath) ?>?type=<?= e($type) ?>&q=<?= urlencode($search) ?>">Все</a>
            <?php foreach ($letters as $navLetter): ?>
                <a
                    class="alphabet-nav__item<?= $letter === $navLetter ? ' is-active' : '' ?>"
                    href="<?= e($selfPath) ?>?type=<?= e($type) ?>&letter=<?= urlencode($navLetter) ?>&q=<?= urlencode($search) ?>"
                ><?= e($navLetter) ?></a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="section section--compact">
        <div class="catalog-grid<?= $layout === 'compact' ? ' catalog-grid--compact' : '' ?>" data-catalog>
            <?php foreach ($people as $person): ?>
                <?php $headline = person_headline_parts((string) ($person['headline'] ?? '')); ?>
                <article
                    class="person-card<?= $layout === 'compact' ? ' person-card--compact' : '' ?>"
                    data-name="<?= e(mb_strtolower($person['full_name'])) ?>"
                    data-search="<?= e(mb_strtolower($person['search_text'])) ?>"
                    data-letter="<?= e($person['alphabet_letter']) ?>"
                >
                    <a class="person-card__link<?= $layout === 'compact' ? ' person-card__link--compact' : '' ?>" href="<?= e($personPath) ?>?type=<?= e($type) ?>&slug=<?= e($person['slug']) ?>">
                        <?php if ($layout === 'compact'): ?>
                            <div class="person-card__compact-media">
                                <?php if (!empty($person['image_path'])): ?>
                                    <img src="<?= e('/' . ltrim($person['image_path'], '/')) ?>" alt="<?= e($person['full_name']) ?>">
                                <?php else: ?>
                                    <div class="person-card__placeholder person-card__placeholder--compact"><?= e($person['alphabet_letter']) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="person-card__media">
                                <?php if (!empty($person['image_path'])): ?>
                                    <img src="<?= e('/' . ltrim($person['image_path'], '/')) ?>" alt="<?= e($person['full_name']) ?>">
                                <?php else: ?>
                                    <div class="person-card__placeholder"><?= e($person['alphabet_letter']) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <div class="person-card__body<?= $layout === 'compact' ? ' person-card__body--compact' : '' ?>">
                            <span class="person-card__type"><?= e(type_label_singular($type)) ?></span>
                            <h2><?= e($person['full_name']) ?></h2>
                            <?php if ($headline['years'] !== ''): ?>
                                <p class="person-card__years"><?= e($headline['years']) ?></p>
                            <?php endif; ?>
                            <?php if ($headline['text'] !== ''): ?>
                                <p><?= e(excerpt($headline['text'], $layout === 'compact' ? 180 : 150)) ?></p>
                            <?php endif; ?>
                            <span class="button button--ghost person-card__cta">Открыть карточку</span>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($people === []): ?>
            <div class="empty-state">
                <h2><?= e($emptyTitle) ?></h2>
                <p>Попробуйте изменить поисковый запрос или убрать фильтр по букве.</p>
            </div>
        <?php endif; ?>

        <div class="catalog-actions">
            <a class="button button--ghost" href="/index.php">На главную</a>
        </div>
    </section>
</main>

<script src="<?= e(asset('assets/js/catalog.js')) ?>" defer></script>
</body>
</html>

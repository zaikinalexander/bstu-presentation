<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

$rectors = rector_entries();
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ректоры | <?= e(config('app.name')) ?></title>
    <meta name="description" content="Ректоры БГТУ им. В.Г. Шухова: биография, научная деятельность и основные награды.">
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
</head>
<body class="page-presentation">
<main class="shell">
    <section class="presentation-hero presentation-hero--overview presentation-hero--history">
        <div>
            <h1>Ректоры<br>БГТУ им. В.Г. Шухова</h1>
        </div>
        <div class="presentation-hero__actions">
            <a class="button button--ghost" href="/index.php">На главную</a>
        </div>
    </section>

    <section class="section section--compact">
        <nav class="rectors-nav" aria-label="Навигация по ректорам">
            <?php foreach ($rectors as $rector): ?>
                <a class="rector-jump" href="#rector-<?= e($rector['slug']) ?>">
                    <img src="<?= e($rector['image']) ?>" alt="<?= e($rector['name']) ?>">
                    <strong><?= e($rector['name']) ?></strong>
                    <span><?= e($rector['nav_meta']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </section>

    <section class="section section--compact">
        <div class="rectors-list">
            <?php foreach ($rectors as $rector): ?>
                <article id="rector-<?= e($rector['slug']) ?>" class="person-sheet rector-card">
                    <div class="person-summary">
                        <div class="person-summary__media">
                            <img src="<?= e($rector['image']) ?>" alt="<?= e($rector['name']) ?>">
                        </div>
                        <div class="person-summary__content">
                            <div class="person-hero__eyebrow">Ректор университета</div>
                            <h2><?= e($rector['name']) ?></h2>
                            <p class="person-hero__headline"><?= e($rector['summary']) ?></p>
                        </div>
                    </div>

                    <div class="person-content rector-card__content">
                        <div class="rector-card__blocks">
                            <?php foreach ($rector['sections'] as $section): ?>
                                <section class="rector-block">
                                    <h3><?= e($section['title']) ?></h3>
                                    <?php foreach ($section['paragraphs'] as $paragraph): ?>
                                        <p><?= e($paragraph) ?></p>
                                    <?php endforeach; ?>
                                </section>
                            <?php endforeach; ?>

                            <?php if ($rector['awards'] !== []): ?>
                                <section class="rector-block">
                                    <h3>Основные награды</h3>
                                    <ul class="rector-awards">
                                        <?php foreach ($rector['awards'] as $award): ?>
                                            <li><?= e($award) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </section>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>
</body>
</html>

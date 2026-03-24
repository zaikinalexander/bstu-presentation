<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

$section = \App\Support\PresentationCatalog::section('education');
$slides = $section['slides'];
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($section['title']) ?> | <?= e(config('app.name')) ?></title>
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
</head>
<body class="page-presentation">
<?= render_environment_banner() ?>
<main class="shell">
    <section class="presentation-hero presentation-hero--history" style="--hero-image:url('<?= e(\App\Support\PresentationCatalog::heroUrl($section['hero'])) ?>')">
        <div>
            <h1><?= e($section['title']) ?></h1>
        </div>
        <div class="presentation-hero__actions">
            <a class="button button--ghost" href="/index.php">На главную</a>
        </div>
    </section>

    <section class="presentation-viewer" data-presentation-viewer>
        <div class="presentation-viewer__stage" data-stage>
            <img data-stage-image src="<?= e($slides[0]['url'] ?? '') ?>" alt="<?= e($slides[0]['alt'] ?? '') ?>">
            <div class="presentation-viewer__controls">
                <button class="button button--ghost" type="button" data-slide-first>В начало</button>
                <button class="button button--ghost" type="button" data-slide-prev>Предыдущий слайд</button>
                <span class="presentation-viewer__counter" data-stage-counter>1 / <?= count($slides) ?></span>
                <button class="button button--ghost" type="button" data-slide-next>Следующий слайд</button>
                <button class="button button--primary" type="button" data-slide-fullscreen>Развернуть на весь экран</button>
                <button class="button button--ghost" type="button" data-slide-exit>Свернуть</button>
            </div>
        </div>

        <div class="presentation-viewer__thumbs">
            <?php foreach ($slides as $slide): ?>
                <button
                    class="presentation-thumb"
                    type="button"
                    data-slide-thumb
                    data-url="<?= e($slide['url']) ?>"
                    data-alt="<?= e($slide['alt']) ?>"
                >
                    <img src="<?= e($slide['url']) ?>" alt="<?= e($slide['alt']) ?>">
                    <span><?= (int) $slide['index'] ?></span>
                </button>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<script src="<?= e(asset('assets/js/presentation.js')) ?>" defer></script>
</body>
</html>

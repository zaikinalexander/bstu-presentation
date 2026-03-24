<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

$cards = [
    [
        'badge' => 'Презентация',
        'title' => 'Строительство и благоустройство',
        'description' => 'Перейдите к материалам о развитии кампуса, благоустройстве территории и современной инфраструктуре университета.',
        'route' => '/stroitelstvo_i_blagoustroistvo/',
        'meta' => count(\App\Support\PresentationCatalog::slides('construction')) . ' слайдов',
    ],
    [
        'badge' => 'Презентация',
        'title' => 'Образовательная и научная деятельность',
        'description' => 'Откройте материалы об образовательных программах, научной работе и направлениях развития университета.',
        'route' => '/obrazovatelnaya_i_nauchnnaya_deyatelnost/',
        'meta' => count(\App\Support\PresentationCatalog::slides('education')) . ' слайдов',
    ],
];
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>История создания и развития | <?= e(config('app.name')) ?></title>
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
</head>
<body class="page-presentation">
<?= render_environment_banner() ?>
<main class="shell">
    <section class="presentation-hero presentation-hero--overview presentation-hero--history">
        <div>
            <h1>История создания и развития<br>БГТУ им. В.Г. Шухова</h1>
        </div>
        <div class="presentation-hero__actions">
            <a class="button button--ghost" href="/index.php">На главную</a>
        </div>
    </section>

    <section class="section section--compact">
        <div class="cards-grid">
            <?php foreach ($cards as $card): ?>
                <a class="section-card" href="<?= e($card['route']) ?>">
                    <h3><?= e($card['title']) ?></h3>
                    <div class="section-card__footer">
                        <span class="button button--ghost section-card__action">Открыть</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
</main>
</body>
</html>

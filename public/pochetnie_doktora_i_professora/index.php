<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

$cards = [
    [
        'title' => 'Почётные доктора<br>БГТУ им. В.Г. Шухова',
        'route' => '/honors2.php?type=doctor',
    ],
    [
        'title' => 'Почётные профессора<br>БГТУ им. В.Г. Шухова',
        'route' => '/honors2.php?type=professor',
    ],
];
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Почётные доктора и профессора | <?= e(config('app.name')) ?></title>
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
</head>
<body class="page-presentation">
<?= render_environment_banner() ?>
<main class="shell">
    <section class="presentation-hero presentation-hero--overview presentation-hero--history">
        <div>
            <h1>Почётные доктора и профессора<br>БГТУ им. В.Г. Шухова</h1>
        </div>
        <div class="presentation-hero__actions">
            <a class="button button--ghost" href="/index.php">На главную</a>
        </div>
    </section>

    <section class="section section--compact">
        <div class="cards-grid">
            <?php foreach ($cards as $card): ?>
                <a class="section-card" href="<?= e($card['route']) ?>">
                    <h3><?= $card['title'] ?></h3>
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

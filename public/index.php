<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$cards = [
    [
        'badge' => 'Раздел сайта',
        'title' => 'История создания и развития университета',
        'description' => 'Перейдите к тематическим материалам о строительстве и благоустройстве, а также об образовательной и научной деятельности университета.',
        'route' => '/istoriya_sozdaniya/',
        'meta' => '2 тематических раздела',
    ],
    [
        'badge' => 'Раздел сайта',
        'title' => 'Ректоры',
        'description' => 'Откройте раздел с карточками ректоров университета и материалами об их биографии, научной и управленческой деятельности.',
        'route' => '/rektory/',
        'meta' => '4 карточки',
    ],
    [
        'badge' => 'Раздел сайта',
        'title' => 'Почётные доктора и профессора',
        'description' => 'Откройте раздел с почётными докторами и профессорами БГТУ им. В.Г. Шухова и перейдите к соответствующему каталогу.',
        'route' => '/pochetnie_doktora_i_professora/',
        'meta' => '2 подраздела',
    ],
];
$title = config('app.name');
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <meta name="description" content="Презентационный сайт БГТУ им. В.Г. Шухова с разделами об истории университета, ректорах, почётных докторах и почётных профессорах.">
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
</head>
<body class="page-home">
<header class="hero">
    <div class="hero__backdrop"></div>
    <div class="hero__content shell">
        <h1 class="sr-only">Презентация БГТУ им. В.Г. Шухова</h1>
        <a class="button button--primary button--hero" href="#sections">Выбрать раздел</a>
    </div>
</header>

<main class="shell">
    <section id="sections" class="section">
        <div class="section__head">
            <div>
                <div class="section__eyebrow">Главная навигация</div>
                <h2>Выбор раздела</h2>
            </div>
        </div>

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

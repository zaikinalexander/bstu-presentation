<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

auth()->require();

$type = ($_GET['type'] ?? '') === 'doctor' || ($_GET['type'] ?? '') === 'professor' ? (string) $_GET['type'] : null;
$people = person_repository()->adminList($type);
$success = flash('success');
$error = flash('error');
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Админка | <?= e(config('app.name')) ?></title>
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
</head>
<body class="page-admin">
<main class="shell admin-shell">
    <section class="admin-header">
        <div>
            <div class="section__eyebrow">Админка</div>
            <h1>Управление сайтом</h1>
        </div>
        <div class="admin-header__actions">
            <a class="button button--ghost" href="/index.php">На сайт</a>
            <a class="button button--ghost" href="/admin/logout.php">Выйти</a>
            <a class="button button--primary" href="/admin/edit.php">Добавить запись</a>
            <a class="button button--primary" href="/admin/presentations.php">Слайды презентаций</a>
        </div>
    </section>

    <div class="admin-panel admin-panel--compact">
        <div class="section__eyebrow">Разделы администрирования</div>
        <h2>Персоналии и презентации</h2>
        <p>В этой админке можно управлять карточками почётных докторов и профессоров, а также отдельными слайдами презентационных разделов. Публичные адреса сайта при этом остаются прежними.</p>
    </div>

    <?php if ($success): ?>
        <div class="flash flash--success"><?= e($success) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="flash flash--error"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="admin-filters">
        <a class="button button--ghost<?= $type === null ? ' is-active' : '' ?>" href="/admin/index.php">Все</a>
        <a class="button button--ghost<?= $type === 'doctor' ? ' is-active' : '' ?>" href="/admin/index.php?type=doctor">Доктора</a>
        <a class="button button--ghost<?= $type === 'professor' ? ' is-active' : '' ?>" href="/admin/index.php?type=professor">Профессора</a>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
            <tr>
                <th>Тип</th>
                <th>Имя</th>
                <th>Буква</th>
                <th>Год</th>
                <th>Статус</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($people as $person): ?>
                <tr>
                    <td><?= e(type_label_singular($person['type'])) ?></td>
                    <td>
                        <strong><?= e($person['full_name']) ?></strong>
                        <?php if (!empty($person['headline'])): ?>
                            <div class="admin-table__sub"><?= e(excerpt($person['headline'], 90)) ?></div>
                        <?php endif; ?>
                    </td>
                    <td><?= e($person['alphabet_letter']) ?></td>
                    <td><?= e($person['year_awarded'] ? (string) $person['year_awarded'] : '—') ?></td>
                    <td><?= !empty($person['is_published']) ? 'Опубликовано' : 'Черновик' ?></td>
                    <td class="admin-table__actions">
                        <a class="button button--ghost" href="/admin/edit.php?id=<?= (int) $person['id'] ?>">Изменить</a>
                        <form method="post" action="/admin/delete.php" onsubmit="return confirm('Удалить запись?');">
                            <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="id" value="<?= (int) $person['id'] ?>">
                            <button class="button button--danger" type="submit">Удалить</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>

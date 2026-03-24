<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use App\Support\PresentationCatalog;

auth()->require();

$sectionKey = (string) ($_GET['section'] ?? 'construction');
$sections = PresentationCatalog::sections();

if (!array_key_exists($sectionKey, $sections)) {
    $sectionKey = 'construction';
}

$slides = presentation_repository()->adminSlides($sectionKey);
$counts = presentation_repository()->countsBySection();
$success = flash('success');
$error = flash('error');
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Слайды презентаций | <?= e(config('app.name')) ?></title>
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
</head>
<body class="page-admin">
<main class="shell admin-shell">
    <section class="admin-header">
        <div>
            <div class="section__eyebrow">Админка</div>
            <h1>Слайды презентаций</h1>
        </div>
        <div class="admin-header__actions">
            <a class="button button--ghost" href="/admin/index.php">К персоналиям</a>
            <a class="button button--ghost" href="/index.php">На сайт</a>
            <a class="button button--primary" href="/admin/presentation_edit.php?section=<?= e($sectionKey) ?>">Добавить слайд</a>
        </div>
    </section>

    <?php if ($success): ?>
        <div class="flash flash--success"><?= e($success) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="flash flash--error"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="admin-filters">
        <?php foreach ($sections as $key => $section): ?>
            <?php $sectionCount = $counts[$key] ?? ['total' => 0, 'published_total' => 0]; ?>
            <a class="button button--ghost<?= $sectionKey === $key ? ' is-active' : '' ?>" href="/admin/presentations.php?section=<?= e($key) ?>">
                <?= e($section['title']) ?> (<?= (int) $sectionCount['published_total'] ?> / <?= (int) $sectionCount['total'] ?>)
            </a>
        <?php endforeach; ?>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
            <tr>
                <th>Превью</th>
                <th>Слайд</th>
                <th>Порядок</th>
                <th>Статус</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($slides as $slide): ?>
                <tr>
                    <td class="admin-preview-cell">
                        <img class="admin-preview" src="<?= e($slide['image_path']) ?>" alt="<?= e($slide['alt_text'] ?: $slide['title']) ?>">
                    </td>
                    <td>
                        <strong><?= e($slide['title'] !== '' ? $slide['title'] : 'Слайд ' . $slide['sort_order']) ?></strong>
                        <?php if (!empty($slide['source_filename'])): ?>
                            <div class="admin-table__sub"><?= e($slide['source_filename']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($slide['alt_text'])): ?>
                            <div class="admin-table__sub"><?= e(excerpt($slide['alt_text'], 90)) ?></div>
                        <?php endif; ?>
                    </td>
                    <td><?= (int) $slide['sort_order'] ?></td>
                    <td><?= !empty($slide['is_published']) ? 'Опубликован' : 'Черновик' ?></td>
                    <td class="admin-table__actions">
                        <a class="button button--ghost" href="/admin/presentation_edit.php?id=<?= (int) $slide['id'] ?>">Изменить</a>
                        <form method="post" action="/admin/presentation_delete.php" onsubmit="return confirm('Удалить слайд?');">
                            <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="id" value="<?= (int) $slide['id'] ?>">
                            <input type="hidden" name="section" value="<?= e($sectionKey) ?>">
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

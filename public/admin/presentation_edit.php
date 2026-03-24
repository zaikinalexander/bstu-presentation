<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use App\Support\PresentationCatalog;

auth()->require();

$sections = PresentationCatalog::sections();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$slide = $id > 0 ? presentation_repository()->find($id) : null;
$defaultSection = (string) ($_GET['section'] ?? 'construction');

if ($id > 0 && $slide === null) {
    flash('error', 'Слайд не найден.');
    redirect('/admin/presentations.php');
}

if (is_post()) {
    if (!verify_csrf($_POST['_token'] ?? null)) {
        flash('error', 'Сессия истекла. Повторите попытку.');
        redirect('/admin/presentations.php');
    }

    $sectionKey = (string) ($_POST['section_key'] ?? 'construction');

    if (!array_key_exists($sectionKey, $sections)) {
        $sectionKey = 'construction';
    }

    $payload = [
        'id' => $_POST['id'] ?? '',
        'section_key' => $sectionKey,
        'title' => (string) ($_POST['title'] ?? ''),
        'alt_text' => (string) ($_POST['alt_text'] ?? ''),
        'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        'image_path' => (string) ($_POST['existing_image_path'] ?? ''),
        'source_filename' => (string) ($_POST['source_filename'] ?? ''),
        'is_published' => $_POST['is_published'] ?? '',
    ];

    if (!empty($_FILES['image']['tmp_name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
        $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION) ?: 'jpg');
        $filename = $sectionKey . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
        $targetDirectory = public_path('uploads/presentations');

        if (!is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0775, true);
        }

        $targetPath = $targetDirectory . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            flash('error', 'Не удалось сохранить изображение.');
            redirect('/admin/presentation_edit.php' . ($id > 0 ? '?id=' . $id : '?section=' . $sectionKey));
        }

        $payload['image_path'] = '/uploads/presentations/' . $filename;

        if ($payload['source_filename'] === '') {
            $payload['source_filename'] = $filename;
        }
    }

    if ($payload['image_path'] === '') {
        flash('error', 'Изображение обязательно.');
        redirect('/admin/presentation_edit.php' . ($id > 0 ? '?id=' . $id : '?section=' . $sectionKey));
    }

    presentation_repository()->save($payload);
    flash('success', 'Слайд сохранён.');
    redirect('/admin/presentations.php?section=' . $sectionKey);
}

$record = $slide ?: [
    'id' => '',
    'section_key' => array_key_exists($defaultSection, $sections) ? $defaultSection : 'construction',
    'title' => '',
    'alt_text' => '',
    'image_path' => '',
    'source_filename' => '',
    'sort_order' => 1,
    'is_published' => 1,
];

$error = flash('error');
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($record['id'] ? 'Редактирование слайда' : 'Новый слайд') ?> | <?= e(config('app.name')) ?></title>
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
</head>
<body class="page-admin">
<main class="shell admin-shell">
    <section class="admin-header">
        <div>
            <div class="section__eyebrow">Админка</div>
            <h1><?= e($record['id'] ? 'Редактирование слайда' : 'Новый слайд') ?></h1>
        </div>
        <div class="admin-header__actions">
            <a class="button button--ghost" href="/admin/presentations.php?section=<?= e($record['section_key']) ?>">К списку слайдов</a>
        </div>
    </section>

    <?php if ($error): ?>
        <div class="flash flash--error"><?= e($error) ?></div>
    <?php endif; ?>

    <form class="editor-grid" method="post" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="id" value="<?= e((string) $record['id']) ?>">
        <input type="hidden" name="existing_image_path" value="<?= e((string) $record['image_path']) ?>">

        <label class="form-field">
            <span>Раздел</span>
            <select name="section_key">
                <?php foreach ($sections as $key => $section): ?>
                    <option value="<?= e($key) ?>"<?= $record['section_key'] === $key ? ' selected' : '' ?>><?= e($section['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="form-field">
            <span>Порядок</span>
            <input type="number" name="sort_order" value="<?= e((string) $record['sort_order']) ?>" min="1" required>
        </label>

        <label class="form-field form-field--wide">
            <span>Название слайда</span>
            <input type="text" name="title" value="<?= e((string) $record['title']) ?>" placeholder="Например: Слайд 12">
        </label>

        <label class="form-field form-field--wide">
            <span>Описание / alt-текст</span>
            <textarea name="alt_text" rows="3"><?= e((string) $record['alt_text']) ?></textarea>
        </label>

        <label class="form-field form-field--wide">
            <span>Исходное имя файла</span>
            <input type="text" name="source_filename" value="<?= e((string) $record['source_filename']) ?>">
        </label>

        <label class="form-field form-field--wide">
            <span>Изображение слайда</span>
            <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
            <?php if (!empty($record['image_path'])): ?>
                <span class="form-field__hint">Текущее изображение: <?= e((string) $record['image_path']) ?></span>
                <img class="admin-preview admin-preview--large" src="<?= e((string) $record['image_path']) ?>" alt="">
            <?php endif; ?>
        </label>

        <label class="toggle-field form-field--wide">
            <input type="checkbox" name="is_published" value="1"<?= !empty($record['is_published']) ? ' checked' : '' ?>>
            <span>Показывать слайд на сайте</span>
        </label>

        <div class="editor-grid__actions">
            <button class="button button--primary" type="submit">Сохранить</button>
            <a class="button button--ghost" href="/admin/presentations.php?section=<?= e($record['section_key']) ?>">Отмена</a>
        </div>
    </form>
</main>
</body>
</html>

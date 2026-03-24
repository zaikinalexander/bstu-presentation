<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

auth()->require();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$person = $id > 0 ? person_repository()->find($id) : null;

if ($id > 0 && $person === null) {
    flash('error', 'Запись не найдена.');
    redirect('/admin/index.php');
}

if (is_post()) {
    if (!verify_csrf($_POST['_token'] ?? null)) {
        flash('error', 'Сессия истекла. Повторите попытку.');
        redirect($id > 0 ? '/admin/edit.php?id=' . $id : '/admin/edit.php');
    }

    $payload = [
        'id' => $_POST['id'] ?? '',
        'type' => ($_POST['type'] ?? 'doctor') === 'professor' ? 'professor' : 'doctor',
        'full_name' => (string) ($_POST['full_name'] ?? ''),
        'slug' => (string) ($_POST['slug'] ?? ''),
        'sort_name' => (string) ($_POST['sort_name'] ?? ''),
        'alphabet_letter' => (string) ($_POST['alphabet_letter'] ?? ''),
        'headline' => (string) ($_POST['headline'] ?? ''),
        'biography' => (string) ($_POST['biography'] ?? ''),
        'award_note' => (string) ($_POST['award_note'] ?? ''),
        'year_awarded' => (string) ($_POST['year_awarded'] ?? ''),
        'source_index' => (string) ($_POST['source_index'] ?? ''),
        'image_path' => (string) ($_POST['existing_image_path'] ?? ''),
        'is_published' => $_POST['is_published'] ?? '',
    ];

    if (!empty($_FILES['image']['tmp_name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
        $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION) ?: 'jpg');
        $filename = slugify($payload['full_name'] !== '' ? $payload['full_name'] : 'person') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
        $targetDirectory = public_path('uploads/manual');

        if (!is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0775, true);
        }

        $targetPath = $targetDirectory . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            flash('error', 'Не удалось сохранить изображение.');
            redirect($id > 0 ? '/admin/edit.php?id=' . $id : '/admin/edit.php');
        }

        $payload['image_path'] = 'uploads/manual/' . $filename;
    }

    if (normalized_whitespace($payload['full_name']) === '') {
        flash('error', 'Имя обязательно.');
        redirect($id > 0 ? '/admin/edit.php?id=' . $id : '/admin/edit.php');
    }

    person_repository()->save($payload);
    flash('success', 'Запись сохранена.');
    redirect('/admin/index.php?type=' . $payload['type']);
}

$record = $person ?: [
    'id' => '',
    'type' => 'doctor',
    'full_name' => '',
    'slug' => '',
    'sort_name' => '',
    'alphabet_letter' => '',
    'headline' => '',
    'biography' => '',
    'award_note' => '',
    'year_awarded' => '',
    'source_index' => '',
    'image_path' => '',
    'is_published' => 1,
];

$error = flash('error');
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($record['id'] ? 'Редактирование' : 'Новая запись') ?> | <?= e(config('app.name')) ?></title>
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
</head>
<body class="page-admin">
<?= render_environment_banner() ?>
<main class="shell admin-shell">
    <section class="admin-header">
        <div>
            <div class="section__eyebrow">Админка</div>
            <h1><?= e($record['id'] ? 'Редактирование карточки' : 'Новая карточка') ?></h1>
        </div>
        <div class="admin-header__actions">
            <a class="button button--ghost" href="/admin/index.php">К списку</a>
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
            <span>Тип</span>
            <select name="type">
                <option value="doctor"<?= $record['type'] === 'doctor' ? ' selected' : '' ?>>Почётный доктор</option>
                <option value="professor"<?= $record['type'] === 'professor' ? ' selected' : '' ?>>Почётный профессор</option>
            </select>
        </label>

        <label class="form-field form-field--wide">
            <span>Полное имя</span>
            <input type="text" name="full_name" value="<?= e((string) $record['full_name']) ?>" required>
        </label>

        <label class="form-field">
            <span>Slug</span>
            <input type="text" name="slug" value="<?= e((string) $record['slug']) ?>" placeholder="Можно оставить пустым">
        </label>

        <label class="form-field">
            <span>Сортировка</span>
            <input type="text" name="sort_name" value="<?= e((string) $record['sort_name']) ?>" placeholder="Например: Алфёров Жорес Иванович">
        </label>

        <label class="form-field">
            <span>Буква навигации</span>
            <input type="text" name="alphabet_letter" value="<?= e((string) $record['alphabet_letter']) ?>" maxlength="2" placeholder="А">
        </label>

        <label class="form-field">
            <span>Год присвоения</span>
            <input type="number" name="year_awarded" value="<?= e((string) $record['year_awarded']) ?>" min="0" max="2100" placeholder="Можно оставить пустым или указать 0">
        </label>

        <label class="form-field">
            <span>Порядковый номер из исходника</span>
            <input type="number" name="source_index" value="<?= e((string) $record['source_index']) ?>" min="1">
        </label>

        <label class="form-field form-field--wide">
            <span>Короткое описание</span>
            <textarea name="headline" rows="3"><?= e((string) $record['headline']) ?></textarea>
        </label>

        <label class="form-field form-field--wide">
            <span>Биография</span>
            <textarea name="biography" rows="12"><?= e((string) $record['biography']) ?></textarea>
        </label>

        <label class="form-field form-field--wide">
            <span>Строка о статусе</span>
            <textarea name="award_note" rows="3"><?= e((string) $record['award_note']) ?></textarea>
        </label>

        <label class="form-field form-field--wide">
            <span>Изображение</span>
            <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
            <?php if (!empty($record['image_path'])): ?>
                <span class="form-field__hint">Текущее: <?= e((string) $record['image_path']) ?></span>
            <?php endif; ?>
        </label>

        <label class="toggle-field form-field--wide">
            <input type="checkbox" name="is_published" value="1"<?= !empty($record['is_published']) ? ' checked' : '' ?>>
            <span>Опубликовать запись на сайте</span>
        </label>

        <div class="editor-grid__actions">
            <button class="button button--primary" type="submit">Сохранить</button>
            <a class="button button--ghost" href="/admin/index.php">Отмена</a>
        </div>
    </form>
</main>
</body>
</html>

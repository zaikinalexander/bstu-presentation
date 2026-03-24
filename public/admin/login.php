<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

if (auth()->check()) {
    redirect('/admin/index.php');
}

if (is_post()) {
    if (!verify_csrf($_POST['_token'] ?? null)) {
        flash('error', 'Сессия истекла. Повторите вход.');
        redirect('/admin/login.php');
    }

    $username = normalized_whitespace((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (auth()->attempt($username, $password)) {
        flash('success', 'Вход выполнен.');
        redirect('/admin/index.php');
    }

    flash('error', 'Неверный логин или пароль.');
    redirect('/admin/login.php');
}

$error = flash('error');
$success = flash('success');
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Админка | <?= e(config('app.name')) ?></title>
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
</head>
<body class="page-admin-login">
<?= render_environment_banner() ?>
<main class="shell admin-login">
    <form class="admin-panel" method="post">
        <div class="section__eyebrow">Административный вход</div>
        <h1>Редактирование разделов</h1>
        <p>Используйте учётные данные из файла `.env`. После первого запуска пароль лучше сменить.</p>
        <?php if (!is_production_environment()): ?>
            <div class="environment-note">
                <strong><?= e(environment_label()) ?>-контур</strong>
                <p><?= e(environment_description()) ?> Текущий адрес: <?= e(app_url_host()) ?>.</p>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="flash flash--error"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="flash flash--success"><?= e($success) ?></div>
        <?php endif; ?>

        <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">

        <label class="form-field">
            <span>Логин</span>
            <input type="text" name="username" autocomplete="username" required>
        </label>

        <label class="form-field">
            <span>Пароль</span>
            <input type="password" name="password" autocomplete="current-password" required>
        </label>

        <button class="button button--primary" type="submit">Войти</button>
        <a class="button button--ghost" href="/index.php">На сайт</a>
    </form>
</main>
</body>
</html>

<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

auth()->require();

if (!is_post() || !verify_csrf($_POST['_token'] ?? null)) {
    flash('error', 'Некорректный запрос.');
    redirect('/admin/index.php');
}

$id = (int) ($_POST['id'] ?? 0);

if ($id > 0) {
    person_repository()->delete($id);
    flash('success', 'Запись удалена.');
}

redirect('/admin/index.php');

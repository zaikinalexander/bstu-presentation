<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

auth()->require();

if (!is_post() || !verify_csrf($_POST['_token'] ?? null)) {
    flash('error', 'Некорректный запрос.');
    redirect('/admin/presentations.php');
}

$id = (int) ($_POST['id'] ?? 0);
$section = normalized_whitespace((string) ($_POST['section'] ?? 'construction'));

if ($id > 0) {
    presentation_repository()->delete($id);
    flash('success', 'Слайд удалён.');
}

redirect('/admin/presentations.php?section=' . ($section !== '' ? $section : 'construction'));

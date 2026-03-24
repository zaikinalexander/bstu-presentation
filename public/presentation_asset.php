<?php

declare(strict_types=1);

$file = basename((string) ($_GET['file'] ?? ''));
$path = dirname(__DIR__) . '/bstupresent/images/' . $file;

if ($file === '' || !is_file($path)) {
    http_response_code(404);
    echo 'Not found';
    exit;
}

$extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$mime = match ($extension) {
    'jpg', 'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'webp' => 'image/webp',
    'gif' => 'image/gif',
    'ico' => 'image/x-icon',
    default => 'application/octet-stream',
};

header('Content-Type: ' . $mime);
header('Content-Length: ' . (string) filesize($path));
header('Cache-Control: public, max-age=86400');
readfile($path);

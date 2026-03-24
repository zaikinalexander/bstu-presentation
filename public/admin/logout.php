<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

auth()->logout();
flash('success', 'Вы вышли из админки.');
redirect('/admin/login.php');

<?php

declare(strict_types=1);

namespace App\Core;

final class Auth
{
    public function __construct(
        private readonly string $username,
        private readonly string $password
    ) {
    }

    public function attempt(string $username, string $password): bool
    {
        if ($username !== $this->username) {
            return false;
        }

        $valid = str_starts_with($this->password, '$2')
            ? password_verify($password, $this->password)
            : hash_equals($this->password, $password);

        if ($valid) {
            $_SESSION['_admin_logged_in'] = true;
            $_SESSION['_admin_username'] = $username;
        }

        return $valid;
    }

    public function check(): bool
    {
        return (bool) ($_SESSION['_admin_logged_in'] ?? false);
    }

    public function require(): void
    {
        if (!$this->check()) {
            flash('error', 'Нужно войти в админку.');
            redirect('/admin/login.php');
        }
    }

    public function logout(): void
    {
        unset($_SESSION['_admin_logged_in'], $_SESSION['_admin_username']);
    }
}

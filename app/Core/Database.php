<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    public static function connect(array $config): PDO
    {
        $driver = $config['driver'] ?? 'sqlite';

        if ($driver === 'mysql') {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $config['host'] ?? '127.0.0.1',
                $config['port'] ?? '3306',
                $config['database'] ?? '',
                $config['charset'] ?? 'utf8mb4'
            );

            $username = $config['username'] ?? '';
            $password = $config['password'] ?? '';
        } else {
            $database = $config['database'] ?? storage_path('database.sqlite');
            $directory = dirname($database);

            if (!is_dir($directory)) {
                mkdir($directory, 0775, true);
            }

            $dsn = 'sqlite:' . $database;
            $username = null;
            $password = null;
        }

        try {
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            throw new PDOException('Database connection failed: ' . $exception->getMessage(), (int) $exception->getCode(), $exception);
        }

        if ($driver === 'sqlite') {
            $pdo->exec('PRAGMA foreign_keys = ON');
        }

        return $pdo;
    }
}

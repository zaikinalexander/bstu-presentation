<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$schema = file_get_contents(base_path('database/schema.sql'));

if ($schema === false) {
    fwrite(STDERR, "Schema file not found.\n");
    exit(1);
}

$driver = config('db.driver');

if ($driver === 'mysql') {
    db()->exec(
        'CREATE TABLE IF NOT EXISTS people (
            id INTEGER PRIMARY KEY AUTO_INCREMENT,
            type VARCHAR(32) NOT NULL,
            full_name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            sort_name VARCHAR(255) NOT NULL,
            alphabet_letter VARCHAR(8) NOT NULL,
            headline TEXT NULL,
            biography LONGTEXT NULL,
            award_note TEXT NULL,
            image_path VARCHAR(255) DEFAULT NULL,
            year_awarded INTEGER DEFAULT NULL,
            source_index INTEGER DEFAULT NULL,
            search_text LONGTEXT NULL,
            is_published TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY idx_people_type_slug (type, slug),
            KEY idx_people_type_sort (type, sort_name),
            KEY idx_people_letter (type, alphabet_letter)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    db()->exec(
        'CREATE TABLE IF NOT EXISTS presentation_slides (
            id INTEGER PRIMARY KEY AUTO_INCREMENT,
            section_key VARCHAR(64) NOT NULL,
            title VARCHAR(255) DEFAULT "",
            alt_text VARCHAR(255) DEFAULT "",
            image_path VARCHAR(255) NOT NULL,
            source_filename VARCHAR(255) DEFAULT "",
            sort_order INTEGER NOT NULL DEFAULT 0,
            is_published TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            KEY idx_presentation_section_order (section_key, sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    fwrite(STDOUT, "Migration completed using {$driver}.\n");
    exit(0);
}

db()->exec($schema);

fwrite(STDOUT, "Migration completed using {$driver}.\n");

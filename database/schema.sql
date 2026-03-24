CREATE TABLE IF NOT EXISTS people (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    type VARCHAR(32) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    sort_name VARCHAR(255) NOT NULL,
    alphabet_letter VARCHAR(8) NOT NULL,
    headline TEXT DEFAULT '',
    biography TEXT DEFAULT '',
    award_note TEXT DEFAULT '',
    image_path VARCHAR(255) DEFAULT NULL,
    year_awarded INTEGER DEFAULT NULL,
    source_index INTEGER DEFAULT NULL,
    search_text TEXT DEFAULT '',
    is_published INTEGER NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE UNIQUE INDEX IF NOT EXISTS idx_people_type_slug ON people (type, slug);
CREATE INDEX IF NOT EXISTS idx_people_type_sort ON people (type, sort_name);
CREATE INDEX IF NOT EXISTS idx_people_letter ON people (type, alphabet_letter);

CREATE TABLE IF NOT EXISTS presentation_slides (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    section_key VARCHAR(64) NOT NULL,
    title VARCHAR(255) DEFAULT '',
    alt_text VARCHAR(255) DEFAULT '',
    image_path VARCHAR(255) NOT NULL,
    source_filename VARCHAR(255) DEFAULT '',
    sort_order INTEGER NOT NULL DEFAULT 0,
    is_published INTEGER NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_presentation_section_order ON presentation_slides (section_key, sort_order);

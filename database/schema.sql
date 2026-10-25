CREATE TABLE IF NOT EXISTS saves (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    category VARCHAR(20) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    progress TEXT NOT NULL,
    next_step TEXT NOT NULL,
    memo TEXT NOT NULL,
    created_at DATETIME(6) NOT NULL,
    updated_at DATETIME(6) NOT NULL,
    version INT UNSIGNED NOT NULL DEFAULT 1,
    INDEX saves_status_updated (status, updated_at, id),
    INDEX saves_category (category),
    CONSTRAINT saves_category_valid CHECK (category IN ('game','reading','creative','study','other')),
    CONSTRAINT saves_status_valid CHECK (status IN ('active','paused','completed'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

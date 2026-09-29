<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class SqliteStore
{
    public function ensure(): void
    {
        DB::unprepared('CREATE TABLE IF NOT EXISTS departments (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, dept_key VARCHAR(120) NOT NULL UNIQUE, code INT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS teachers (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, department_id BIGINT UNSIGNED NOT NULL, emp_id VARCHAR(120) NULL, name VARCHAR(255) NOT NULL, designation VARCHAR(255) NULL, institute1 TEXT NULL, institute2 TEXT NULL, email VARCHAR(255) NULL, office_ext VARCHAR(120) NULL, encrypted_id VARCHAR(255) NULL, photo_path TEXT NULL, sort_order INT NOT NULL DEFAULT 0, profile_json LONGTEXT NULL, created_at DATETIME NULL, updated_at DATETIME NULL, INDEX teachers_department_id_index (department_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS news_posts (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, wp_id BIGINT NULL UNIQUE, slug VARCHAR(255) NOT NULL UNIQUE, title VARCHAR(255) NOT NULL, display_date VARCHAR(120) NULL, published_at DATETIME NULL, image_path TEXT NULL, content LONGTEXT NULL, gallery_json LONGTEXT NULL, content_after LONGTEXT NULL, created_at DATETIME NULL, updated_at DATETIME NULL, INDEX news_posts_published_at_index (published_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS notice_posts (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, wp_id BIGINT NULL, slug VARCHAR(255) NOT NULL UNIQUE, title VARCHAR(255) NOT NULL, display_date VARCHAR(120) NULL, day_label VARCHAR(120) NULL, month_label VARCHAR(120) NULL, published_at DATETIME NULL, image_path TEXT NULL, content LONGTEXT NULL, created_at DATETIME NULL, updated_at DATETIME NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    public function select(string $sql, array $params = []): array
    {
        return array_map(static fn ($row): array => (array) $row, DB::select($sql, $params));
    }

    public function execute(string $sql, array $params = []): array
    {
        DB::statement($sql, $params);

        return ['ok' => true, 'lastrowid' => (int) DB::getPdo()->lastInsertId()];
    }
}

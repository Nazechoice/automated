<?php
declare(strict_types=1);

namespace App\models;

use App\lib\Database;

final class Setting
{
    private const CREATE_TABLE_SQL = <<<SQL
CREATE TABLE IF NOT EXISTS settings (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  setting_key VARCHAR(120) NOT NULL,
  setting_value LONGTEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_settings_key (setting_key)
) ENGINE=InnoDB
SQL;

    public static function defaults(): array
    {
        return [
            'system_name' => 'Automobile Management System',
            'logo_path' => '',
            'contact_email' => 'info@automates.local',
            'contact_phone' => '+234 800 000 0000',
            'contact_address' => 'Lagos, Nigeria',
            'footer_text' => 'Premium dealership platform built for modern car management.',
            'homepage_title' => 'Discover Your Perfect Ride',
            'homepage_subtitle' => 'Explore our exclusive collection of premium vehicles handpicked for luxury and performance',
            'homepage_about' => 'Premium automobile management and dealership platform.',
        ];
    }

    public static function all(): array
    {
        $pdo = Database::pdo();
        try {
            self::ensureTable();
            $rows = $pdo->query('SELECT setting_key, setting_value, updated_at FROM settings ORDER BY setting_key ASC')->fetchAll();
        } catch (\Throwable $e) {
            return self::defaults();
        }

        $settings = [];
        foreach ($rows as $row) {
            $settings[(string)$row['setting_key']] = (string)($row['setting_value'] ?? '');
        }

        return $settings;
    }

    public static function allMerged(): array
    {
        return array_merge(self::defaults(), self::all());
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $pdo = Database::pdo();
        try {
            self::ensureTable();
            $st = $pdo->prepare('SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1');
            $st->execute([$key]);
            $value = $st->fetchColumn();
        } catch (\Throwable $e) {
            return $default;
        }

        return $value === false ? $default : $value;
    }

    public static function set(string $key, string $value): void
    {
        $pdo = Database::pdo();
        self::ensureTable();
        self::upsert($pdo, $key, $value);
    }

    public static function setMany(array $settings): void
    {
        $pdo = Database::pdo();
        self::ensureTable();
        $pdo->beginTransaction();

        try {
            foreach ($settings as $key => $value) {
                self::upsert($pdo, (string)$key, (string)$value);
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    private static function ensureTable(): void
    {
        $pdo = Database::pdo();
        $pdo->exec(self::CREATE_TABLE_SQL);
    }

    private static function upsert(\PDO $pdo, string $key, string $value): void
    {
        $st = $pdo->prepare(
            'INSERT INTO settings (setting_key, setting_value)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP'
        );
        $st->execute([$key, $value]);
    }
}

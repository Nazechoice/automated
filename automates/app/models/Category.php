<?php
declare(strict_types=1);

namespace App\models;

use App\lib\Database;

final class Category
{
    public static function all(bool $includeInactive = true, ?string $query = null): array
    {
        $pdo = Database::pdo();
        // NOTE: `categories` table (see setup/setup.sql) does NOT have a `status` column.
        // Treat all categories as active.
        $sql = "
            SELECT
                c.id,
                c.name,
                c.slug,
                c.created_at,
                c.updated_at,
                COUNT(v.id) AS vehicle_count
            FROM categories c
            LEFT JOIN vehicles v ON v.category_id = c.id
            WHERE 1=1
        ";
        $params = [];

        $query = trim((string)$query);
        if ($query !== '') {
            $sql .= ' AND (c.name LIKE ? OR c.slug LIKE ?)';
            $like = '%' . $query . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= "
            GROUP BY c.id, c.name, c.slug, c.created_at, c.updated_at
            ORDER BY c.name ASC
        ";
        $st = $pdo->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public static function active(): array
    {
        return self::all(false);
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare('SELECT * FROM categories WHERE id = ? LIMIT 1');
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public static function counts(): array
    {
        $pdo = Database::pdo();
        // categories has no `status` column in the current schema; treat all as active.
        $rows = $pdo->query(
            "SELECT
                COUNT(*) AS total_categories,
                COUNT(*) AS active_categories,
                COALESCE(SUM(vehicle_count), 0) AS total_assigned
             FROM (
                SELECT c.id, COUNT(v.id) AS vehicle_count
                FROM categories c
                LEFT JOIN vehicles v ON v.category_id = c.id
                GROUP BY c.id
             ) t"
        )->fetch() ?: [];

        return [
            'total_categories' => (int)($rows['total_categories'] ?? 0),
            'active_categories' => (int)($rows['active_categories'] ?? 0),
            'total_assigned' => (int)($rows['total_assigned'] ?? 0),
        ];
    }


    public static function create(string $name, ?string $slug = null): int
    {
        $pdo = Database::pdo();
        $slug = self::normalizeSlug($slug ?: $name);

        // categories table has no `status` column in this project.
        $st = $pdo->prepare('INSERT INTO categories (name, slug) VALUES (?, ?)');
        $st->execute([$name, $slug]);

        return (int)$pdo->lastInsertId();
    }


    public static function update(int $id, string $name, ?string $slug = null): void
    {
        $pdo = Database::pdo();
        $slug = self::normalizeSlug($slug ?: $name);

        $st = $pdo->prepare('UPDATE categories SET name = ?, slug = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $st->execute([$name, $slug, $id]);
    }

    public static function updateStatus(int $id, string $status): void
    {
        // categories table has no `status` column in this project schema.
        // Keep this method to avoid fatal errors from existing calls.
        throw new \BadMethodCallException('Category status is not supported by the current database schema.');
    }


    public static function delete(int $id): void
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare('DELETE FROM categories WHERE id = ?');
        $st->execute([$id]);
    }

    public static function normalizeSlug(string $value): string
    {
        $slug = strtolower(trim($value));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        $slug = trim($slug, '-');
        if ($slug === '') {
            throw new \InvalidArgumentException('Category slug cannot be empty');
        }
        return $slug;
    }
}

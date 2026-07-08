<?php
declare(strict_types=1);

namespace App\models;

use App\lib\Database;

final class Vehicle
{
    public static function categories(): array
    {
        $pdo = Database::pdo();
        $st = $pdo->query('SELECT id, name, slug FROM categories ORDER BY name ASC');
        return $st->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare(
            'SELECT v.*, c.name AS category_name
             FROM vehicles v
             LEFT JOIN categories c ON c.id = v.category_id
             WHERE v.id = ? LIMIT 1'
        );
        $st->execute([$id]);
        $row = $st->fetch();
        if (!$row) {
            return null;
        }

        $row['images'] = self::images($id);
        $row['attributes'] = self::attributes($id);
        $row['stock_qty'] = self::inventoryQty($id);
        $row['availability_status'] = self::availabilityStatus($row, (int)$row['stock_qty']);
        return $row;
    }

    public static function images(int $vehicleId): array
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare(
            'SELECT id, image_url, sort_order
             FROM vehicle_images
             WHERE vehicle_id = ?
             ORDER BY sort_order ASC, id ASC'
        );
        $st->execute([$vehicleId]);
        return $st->fetchAll();
    }

    public static function featured(int $limit = 3): array
    {
        $pdo = Database::pdo();
        $sql = "
            SELECT
                v.id,
                v.brand,
                v.model,
                v.year,
                v.price,
                v.currency,
                v.mileage_km,
                v.fuel_type,
                v.transmission,
                v.featured,
                v.status,
                COALESCE(i.stock_qty, 1) AS stock_qty,
                c.name AS category_name,
                COALESCE(
                    (SELECT vi.image_url
                     FROM vehicle_images vi
                     WHERE vi.vehicle_id = v.id
                     ORDER BY vi.sort_order ASC, vi.id ASC
                     LIMIT 1),
                    'assets/img/vehicle-luxury.svg'
                ) AS cover
            FROM vehicles v
            LEFT JOIN categories c ON c.id = v.category_id
            LEFT JOIN inventory_items i ON i.vehicle_id = v.id
            WHERE v.status = 'active'
            ORDER BY v.featured DESC, v.created_at DESC
            LIMIT " . (int)$limit . "
        ";
        $st = $pdo->query($sql);
        return $st->fetchAll();
    }

    public static function active(int $limit = 6): array
    {
        return self::recentActive($limit);
    }

    public static function recentActive(int $limit = 6): array
    {
        $pdo = Database::pdo();
        $sql = "
            SELECT
                v.id,
                v.brand,
                v.model,
                v.year,
                v.price,
                v.currency,
                v.mileage_km,
                v.fuel_type,
                v.transmission,
                v.featured,
                v.status,
                COALESCE(i.stock_qty, 1) AS stock_qty,
                c.name AS category_name,
                COALESCE(
                    (SELECT vi.image_url
                     FROM vehicle_images vi
                     WHERE vi.vehicle_id = v.id
                     ORDER BY vi.sort_order ASC, vi.id ASC
                     LIMIT 1),
                    'assets/img/vehicle-luxury.svg'
                ) AS cover
            FROM vehicles v
            LEFT JOIN categories c ON c.id = v.category_id
            LEFT JOIN inventory_items i ON i.vehicle_id = v.id
            WHERE v.status = 'active'
            ORDER BY v.created_at DESC, v.id DESC
            LIMIT " . (int)$limit . "
        ";
        $st = $pdo->query($sql);
        return $st->fetchAll();
    }

    public static function allWithCover(int $limit = 25): array
    {
        $pdo = Database::pdo();
        $sql = "
            SELECT
                v.id,
                v.brand,
                v.model,
                v.year,
                v.price,
                v.currency,
                v.mileage_km,
                v.fuel_type,
                v.transmission,
                v.featured,
                v.status,
                COALESCE(i.stock_qty, 1) AS stock_qty,
                c.name AS category_name,
                COALESCE(
                    (SELECT vi.image_url
                     FROM vehicle_images vi
                     WHERE vi.vehicle_id = v.id
                     ORDER BY vi.sort_order ASC, vi.id ASC
                     LIMIT 1),
                    'assets/img/vehicle-luxury.svg'
                ) AS cover
            FROM vehicles v
            LEFT JOIN categories c ON c.id = v.category_id
            LEFT JOIN inventory_items i ON i.vehicle_id = v.id
            ORDER BY v.created_at DESC
            LIMIT " . (int)$limit . "
        ";
        $st = $pdo->query($sql);
        return $st->fetchAll();
    }

    public static function browse(array $filters = [], int $limit = 24): array
    {
        $pdo = Database::pdo();

        $sql = "
            SELECT
                v.id,
                v.brand,
                v.model,
                v.year,
                v.price,
                v.currency,
                v.mileage_km,
                v.fuel_type,
                v.transmission,
                v.featured,
                v.status,
                COALESCE(i.stock_qty, 1) AS stock_qty,
                c.id AS category_id,
                c.name AS category_name,
                c.slug AS category_slug,
                COALESCE(
                    (SELECT vi.image_url
                     FROM vehicle_images vi
                     WHERE vi.vehicle_id = v.id
                     ORDER BY vi.sort_order ASC, vi.id ASC
                     LIMIT 1),
                    'assets/img/vehicle-luxury.svg'
                ) AS cover
            FROM vehicles v
            LEFT JOIN categories c ON c.id = v.category_id
            LEFT JOIN inventory_items i ON i.vehicle_id = v.id
            WHERE 1 = 1
        ";

        $params = [];
        $status = strtolower(trim((string)($filters['status'] ?? 'active')));
        if ($status !== 'all') {
            if (!in_array($status, ['active', 'inactive', 'sold'], true)) {
                $status = 'active';
            }
            $sql .= ' AND v.status = ?';
            $params[] = $status;
        }

        $q = trim((string)($filters['q'] ?? ''));
        if ($q !== '') {
            $sql .= ' AND (v.brand LIKE ? OR v.model LIKE ? OR c.name LIKE ?)';
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $category = trim((string)($filters['category'] ?? ''));
        if ($category !== '') {
            $sql .= ' AND c.slug = ?';
            $params[] = $category;
        }

        $fuel = trim((string)($filters['fuel'] ?? ''));
        if ($fuel !== '') {
            $sql .= ' AND v.fuel_type = ?';
            $params[] = $fuel;
        }

        $transmission = trim((string)($filters['transmission'] ?? ''));
        if ($transmission !== '') {
            $sql .= ' AND v.transmission = ?';
            $params[] = $transmission;
        }

        $min = $filters['min_price'] ?? '';
        if ($min !== '' && is_numeric($min)) {
            $sql .= ' AND v.price >= ?';
            $params[] = (float)$min;
        }

        $max = $filters['max_price'] ?? '';
        if ($max !== '' && is_numeric($max)) {
            $sql .= ' AND v.price <= ?';
            $params[] = (float)$max;
        }

        $sql .= ' ORDER BY v.featured DESC, v.created_at DESC LIMIT ' . (int)$limit;
        $st = $pdo->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public static function related(int $vehicleId, ?int $categoryId = null, int $limit = 4): array
    {
        $pdo = Database::pdo();
        $sql = "
            SELECT
                v.id,
                v.brand,
                v.model,
                v.year,
                v.price,
                v.currency,
                v.mileage_km,
                v.fuel_type,
                v.transmission,
                v.featured,
                v.status,
                COALESCE(i.stock_qty, 1) AS stock_qty,
                c.name AS category_name,
                COALESCE(
                    (SELECT vi.image_url
                     FROM vehicle_images vi
                     WHERE vi.vehicle_id = v.id
                     ORDER BY vi.sort_order ASC, vi.id ASC
                     LIMIT 1),
                    'assets/img/vehicle-luxury.svg'
                ) AS cover
            FROM vehicles v
            LEFT JOIN categories c ON c.id = v.category_id
            LEFT JOIN inventory_items i ON i.vehicle_id = v.id
            WHERE v.status = 'active'
              AND v.id <> ?
        ";
        $params = [$vehicleId];
        if ($categoryId !== null && $categoryId > 0) {
            $sql .= ' AND v.category_id = ?';
            $params[] = $categoryId;
        }
        $sql .= ' ORDER BY v.featured DESC, v.created_at DESC LIMIT ' . (int)$limit;
        $st = $pdo->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public static function counts(): array
    {
        $pdo = Database::pdo();
        $rows = $pdo->query(
            "SELECT
                COUNT(*) AS total_vehicles,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_vehicles,
                SUM(CASE WHEN featured = 1 THEN 1 ELSE 0 END) AS featured_vehicles,
                SUM(CASE WHEN status = 'active' AND COALESCE(i.stock_qty, 1) > 0 THEN 1 ELSE 0 END) AS available_vehicles
             FROM vehicles v
             LEFT JOIN inventory_items i ON i.vehicle_id = v.id"
        )->fetch() ?: [];

        return [
            'total_vehicles' => (int)($rows['total_vehicles'] ?? 0),
            'active_vehicles' => (int)($rows['active_vehicles'] ?? 0),
            'featured_vehicles' => (int)($rows['featured_vehicles'] ?? 0),
            'available_vehicles' => (int)($rows['available_vehicles'] ?? 0),
        ];
    }

    public static function create(array $data, ?array $imageUrls = null): int
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();

        try {
            $st = $pdo->prepare(
                'INSERT INTO vehicles (
                    category_id, brand, model, year, price, currency, mileage_km,
                    fuel_type, transmission, seating_capacity, horsepower, description,
                    status, featured
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                )'
            );

            $st->execute([
                $data['category_id'],
                $data['brand'],
                $data['model'],
                $data['year'],
                $data['price'],
                $data['currency'],
                $data['mileage_km'],
                $data['fuel_type'],
                $data['transmission'],
                $data['seating_capacity'],
                $data['horsepower'],
                $data['description'],
                $data['status'],
                $data['featured'],
            ]);

            $vehicleId = (int)$pdo->lastInsertId();
            self::replaceImages($pdo, $vehicleId, $imageUrls ?? []);
            self::syncInventory($pdo, $vehicleId, 1);

            $pdo->commit();
            return $vehicleId;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function update(int $id, array $data, ?array $imageUrls = null): void
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();

        try {
            $st = $pdo->prepare(
                'UPDATE vehicles SET
                    category_id = ?,
                    brand = ?,
                    model = ?,
                    year = ?,
                    price = ?,
                    currency = ?,
                    mileage_km = ?,
                    fuel_type = ?,
                    transmission = ?,
                    seating_capacity = ?,
                    horsepower = ?,
                    description = ?,
                    status = ?,
                    featured = ?,
                    updated_at = CURRENT_TIMESTAMP
                 WHERE id = ?'
            );

            $st->execute([
                $data['category_id'],
                $data['brand'],
                $data['model'],
                $data['year'],
                $data['price'],
                $data['currency'],
                $data['mileage_km'],
                $data['fuel_type'],
                $data['transmission'],
                $data['seating_capacity'],
                $data['horsepower'],
                $data['description'],
                $data['status'],
                $data['featured'],
                $id,
            ]);

            if ($imageUrls !== null) {
                self::replaceImages($pdo, $id, $imageUrls);
            }

            self::syncInventory($pdo, $id, 1);

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function delete(int $id): void
    {
        $existingImages = self::images($id);
        $pdo = Database::pdo();
        $st = $pdo->prepare('DELETE FROM vehicles WHERE id = ?');
        $st->execute([$id]);
        self::deleteLocalImages(array_column($existingImages, 'image_url'));
    }

    private static function replaceImages(\PDO $pdo, int $vehicleId, array $imageUrls): void
    {
        $pdo->prepare('DELETE FROM vehicle_images WHERE vehicle_id = ?')->execute([$vehicleId]);

        $cleanUrls = array_values(array_filter(array_map('trim', $imageUrls), static fn(string $url): bool => $url !== ''));
        if (empty($cleanUrls)) {
            $cleanUrls = ['assets/img/vehicle-luxury.svg'];
        }

        $insert = $pdo->prepare(
            'INSERT INTO vehicle_images (vehicle_id, image_url, sort_order) VALUES (?, ?, ?)'
        );

        foreach ($cleanUrls as $index => $imageUrl) {
            $insert->execute([$vehicleId, $imageUrl, $index]);
        }
    }

    public static function attributes(int $vehicleId): array
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare(
            'SELECT id, attr_key, attr_value
             FROM vehicle_attributes
             WHERE vehicle_id = ?
             ORDER BY id ASC'
        );
        $st->execute([$vehicleId]);
        return $st->fetchAll();
    }

    public static function syncAttributes(int $vehicleId, array $attributes): void
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();

        try {
            $pdo->prepare('DELETE FROM vehicle_attributes WHERE vehicle_id = ?')->execute([$vehicleId]);

            $insert = $pdo->prepare(
                'INSERT INTO vehicle_attributes (vehicle_id, attr_key, attr_value) VALUES (?, ?, ?)'
            );
            foreach ($attributes as $attribute) {
                $key = trim((string)($attribute['attr_key'] ?? ''));
                $value = trim((string)($attribute['attr_value'] ?? ''));
                if ($key === '' || $value === '') {
                    continue;
                }
                $insert->execute([$vehicleId, $key, $value]);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function deleteImage(int $imageId): ?array
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare('SELECT * FROM vehicle_images WHERE id = ? LIMIT 1');
        $st->execute([$imageId]);
        $row = $st->fetch();
        if (!$row) {
            return null;
        }

        $del = $pdo->prepare('DELETE FROM vehicle_images WHERE id = ?');
        $del->execute([$imageId]);

        $imageUrl = (string)($row['image_url'] ?? '');
        if ($imageUrl !== '' && !preg_match('#^[a-z][a-z0-9+.-]*://#i', $imageUrl) && !str_starts_with($imageUrl, 'assets/')) {
            $projectRoot = dirname(__DIR__, 2);
            $filePath = $projectRoot . '/public/' . ltrim($imageUrl, '/');
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }

        return $row;
    }

    public static function inventoryQty(int $vehicleId): int
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare('SELECT stock_qty FROM inventory_items WHERE vehicle_id = ? LIMIT 1');
        $st->execute([$vehicleId]);
        $value = $st->fetchColumn();
        return $value === false ? 1 : (int)$value;
    }

    public static function syncInventory(\PDO $pdo, int $vehicleId, int $defaultQty = 1): void
    {
        $st = $pdo->prepare(
            'INSERT INTO inventory_items (vehicle_id, stock_qty)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE stock_qty = VALUES(stock_qty)'
        );
        $st->execute([$vehicleId, $defaultQty]);
    }

    public static function availabilityStatus(array $vehicle, int $stockQty): string
    {
        if (($vehicle['status'] ?? 'active') === 'sold') {
            return 'sold';
        }
        if (($vehicle['status'] ?? 'active') !== 'active') {
            return 'inactive';
        }
        return $stockQty > 0 ? 'available' : 'out_of_stock';
    }

    private static function deleteLocalImages(array $imageUrls): void
    {
        $projectRoot = dirname(__DIR__, 2);
        foreach ($imageUrls as $imageUrl) {
            $imageUrl = (string)$imageUrl;
            if ($imageUrl === '' || preg_match('#^[a-z][a-z0-9+.-]*://#i', $imageUrl) || str_starts_with($imageUrl, 'assets/')) {
                continue;
            }

            $filePath = $projectRoot . '/public/' . ltrim($imageUrl, '/');
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }
    }
}

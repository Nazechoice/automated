<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\lib\Auth;
use App\lib\Database;
use App\lib\RateLimiter;

final class VehicleApiController
{
    public function search(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $q = trim((string)($_GET['q'] ?? ''));
        $category = (string)($_GET['category'] ?? '');
        $min = $_GET['min_price'] ?? null;
        $max = $_GET['max_price'] ?? null;

        if (!RateLimiter::allow('vehicle_search', 30, 60)) {
            http_response_code(429);
            echo json_encode(['error' => 'Too many requests']);
            return;
        }

        $pdo = Database::pdo();

        $sql = "SELECT v.id, v.brand, v.model, v.year, v.price, v.fuel_type, v.transmission,
                       (SELECT image_url FROM vehicle_images vi WHERE vi.vehicle_id = v.id ORDER BY sort_order ASC LIMIT 1) AS cover
                FROM vehicles v
                WHERE v.status = 'active' ";
        $params = [];

        if ($q !== '') {
            $sql .= " AND (v.brand LIKE ? OR v.model LIKE ?) ";
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
        }
        if ($category !== '') {
            $sql .= " AND EXISTS (SELECT 1 FROM categories c WHERE c.id = v.category_id AND c.slug = ?) ";
            $params[] = $category;
        }
        if ($min !== null && $min !== '') {
            $sql .= " AND v.price >= ? ";
            $params[] = (float)$min;
        }
        if ($max !== null && $max !== '') {
            $sql .= " AND v.price <= ? ";
            $params[] = (float)$max;
        }

        $sql .= " ORDER BY v.featured DESC, v.created_at DESC LIMIT 12";

        $st = $pdo->prepare($sql);
        $st->execute($params);
        $rows = $st->fetchAll();

        echo json_encode(['items' => $rows]);
    }
}


<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\lib\Auth;
use App\lib\Database;
use App\lib\CSRF;

final class CompareApiController
{
    public function toggle(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        Auth::requireLogin();
        $payload = json_decode(file_get_contents('php://input') ?: '[]', true);
        if (!is_array($payload)) {
            $payload = [];
        }

        CSRF::verify($_POST['csrf_token'] ?? $payload['csrf_token'] ?? null);

        $vehicleId = (int)($_POST['vehicle_id'] ?? $payload['vehicle_id'] ?? 0);
        if ($vehicleId <= 0) {
            http_response_code(422);
            echo json_encode(['error' => 'Invalid vehicle_id']);
            return;
        }

        $pdo = Database::pdo();
        $userId = (int)Auth::id();

        $st = $pdo->prepare('SELECT id FROM vehicle_comparisons WHERE user_id = ? AND vehicle_id = ? LIMIT 1');
        $st->execute([$userId, $vehicleId]);
        $row = $st->fetch();

        if ($row) {
            $del = $pdo->prepare('DELETE FROM vehicle_comparisons WHERE id = ?');
            $del->execute([(int)$row['id']]);
            echo json_encode(['status' => 'removed']);
        } else {
            $ins = $pdo->prepare('INSERT INTO vehicle_comparisons (user_id, vehicle_id) VALUES (?, ?)');
            $ins->execute([$userId, $vehicleId]);
            echo json_encode(['status' => 'added']);
        }
    }
}


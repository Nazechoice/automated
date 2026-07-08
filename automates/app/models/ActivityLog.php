<?php
declare(strict_types=1);

namespace App\models;

use App\lib\Database;

final class ActivityLog
{
    public static function record(?int $actorUserId, string $action, ?string $entityType = null, ?int $entityId = null, array $meta = []): void
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare(
            'INSERT INTO activity_logs (actor_user_id, action, entity_type, entity_id, meta)
             VALUES (?, ?, ?, ?, ?)'
        );
        $st->execute([
            $actorUserId,
            $action,
            $entityType,
            $entityId,
            $meta !== [] ? json_encode($meta, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null,
        ]);
    }

    public static function recent(int $limit = 10): array
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare(
            "SELECT
                a.id,
                a.action,
                a.entity_type,
                a.entity_id,
                a.meta,
                a.created_at,
                COALESCE(u.full_name, 'System') AS actor_name,
                COALESCE(u.role, 'system') AS actor_role
             FROM activity_logs a
             LEFT JOIN users u ON u.id = a.actor_user_id
             ORDER BY a.created_at DESC, a.id DESC
             LIMIT ?"
        );
        $st->bindValue(1, $limit, \PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }
}

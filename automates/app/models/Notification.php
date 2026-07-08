<?php
declare(strict_types=1);

namespace App\models;

use App\lib\Database;

final class Notification
{
    public static function create(int $userId, string $title, ?string $body = null, string $type = 'system'): int
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare(
            'INSERT INTO notifications (user_id, title, body, type, is_read)
             VALUES (?, ?, ?, ?, 0)'
        );
        $st->execute([$userId, $title, $body, self::normalizeType($type)]);

        return (int)$pdo->lastInsertId();
    }

    public static function unreadCount(int $userId): int
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
        $st->execute([$userId]);
        return (int)($st->fetchColumn() ?: 0);
    }

    public static function recentForUser(int $userId, int $limit = 10): array
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare(
            'SELECT id, title, body, type, is_read, created_at
             FROM notifications
             WHERE user_id = ?
             ORDER BY created_at DESC, id DESC
             LIMIT ?'
        );
        $st->bindValue(1, $userId, \PDO::PARAM_INT);
        $st->bindValue(2, $limit, \PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public static function allForUser(int $userId, int $limit = 50): array
    {
        return self::recentForUser($userId, $limit);
    }

    public static function markRead(int $notificationId, int $userId): void
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?');
        $st->execute([$notificationId, $userId]);
    }

    public static function markAllRead(int $userId): void
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?');
        $st->execute([$userId]);
    }

    private static function normalizeType(string $type): string
    {
        $allowed = ['booking', 'test_drive', 'message', 'system', 'sales'];
        return in_array($type, $allowed, true) ? $type : 'system';
    }
}

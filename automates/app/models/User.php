<?php
declare(strict_types=1);

namespace App\models;

use App\lib\Database;

final class User
{
    public int $id;
    public string $role;
    public string $full_name;
    public string $email;
    public ?string $phone;
    public string $status;

    public static function findById(int $id): ?self
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ? self::fromRow($row) : null;
    }

    public static function findByEmail(string $email): ?self
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $st->execute([$email]);
        $row = $st->fetch();
        return $row ? self::fromRow($row) : null;
    }

    public static function all(?string $role = null): array
    {
        $pdo = Database::pdo();
        if ($role === null) {
            $st = $pdo->query('SELECT * FROM users ORDER BY created_at DESC');
        } else {
            $st = $pdo->prepare('SELECT * FROM users WHERE role = ? ORDER BY created_at DESC');
            $st->execute([$role]);
        }
        return $st->fetchAll();
    }

    public static function findByEmailExcept(string $email, int $excludeUserId): ?self
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare('SELECT * FROM users WHERE email = ? AND id <> ? LIMIT 1');
        $st->execute([$email, $excludeUserId]);
        $row = $st->fetch();
        return $row ? self::fromRow($row) : null;
    }

    public static function counts(): array
    {
        $pdo = Database::pdo();
        $rows = $pdo->query(
            "SELECT
                COUNT(*) AS total_users,
                SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) AS admin_users,
                SUM(CASE WHEN role = 'customer' THEN 1 ELSE 0 END) AS customer_users,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_users
             FROM users"
        )->fetch() ?: [];

        return [
            'total_users' => (int)($rows['total_users'] ?? 0),
            'admin_users' => (int)($rows['admin_users'] ?? 0),
            'customer_users' => (int)($rows['customer_users'] ?? 0),
            'active_users' => (int)($rows['active_users'] ?? 0),
        ];
    }

    public static function create(
        string $fullName,
        string $email,
        string $password,
        ?string $phone = null,
        string $role = 'customer',
        string $status = 'active'
    ): self {
        $pdo = Database::pdo();
        $st = $pdo->prepare(
            'INSERT INTO users (role, full_name, email, password_hash, phone, status) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $st->execute([
            $role,
            $fullName,
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            $phone,
            $status,
        ]);

        $user = self::findById((int)$pdo->lastInsertId());
        if (!$user) {
            throw new \RuntimeException('Failed to create user account');
        }

        return $user;
    }

    public static function upsertAdmin(string $fullName, string $email, string $password): self
    {
        $pdo = Database::pdo();
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $st = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $st->execute([$email]);
        $row = $st->fetch();

        if ($row) {
            $up = $pdo->prepare(
                'UPDATE users SET role = ?, full_name = ?, password_hash = ?, status = ?, phone = NULL WHERE email = ?'
            );
            $up->execute(['admin', $fullName, $hash, 'active', $email]);
        } else {
            $ins = $pdo->prepare(
                'INSERT INTO users (role, full_name, email, password_hash, phone, status) VALUES (?, ?, ?, ?, ?, ?)'
            );
            $ins->execute(['admin', $fullName, $email, $hash, null, 'active']);
        }

        $user = self::findByEmail($email);
        if (!$user) {
            throw new \RuntimeException('Failed to create admin account');
        }

        return $user;
    }

    public function verifyPassword(string $password): bool
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare('SELECT password_hash FROM users WHERE id = ? LIMIT 1');
        $st->execute([$this->id]);
        $hash = (string)($st->fetch()['password_hash'] ?? '');
        return $hash !== '' && password_verify($password, $hash);
    }

    public static function updateStatus(int $id, string $status): void
    {
        $allowed = ['active', 'disabled'];
        if (!in_array($status, $allowed, true)) {
            throw new \InvalidArgumentException('Invalid user status');
        }

        $pdo = Database::pdo();
        $st = $pdo->prepare('UPDATE users SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
        $st->execute([$status, $id]);
    }

    public static function updateProfile(int $id, string $fullName, string $email, ?string $phone = null): void
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare(
            'UPDATE users SET full_name = ?, email = ?, phone = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?'
        );
        $st->execute([$fullName, $email, $phone, $id]);
    }

    public static function updatePassword(int $id, string $password): void
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare(
            'UPDATE users SET password_hash = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?'
        );
        $st->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
    }

    private static function fromRow(array $row): self
    {
        $u = new self();
        $u->id = (int)$row['id'];
        $u->role = (string)$row['role'];
        $u->full_name = (string)$row['full_name'];
        $u->email = (string)$row['email'];
        $u->phone = $row['phone'] !== null ? (string)$row['phone'] : null;
        $u->status = (string)$row['status'];
        return $u;
    }
}


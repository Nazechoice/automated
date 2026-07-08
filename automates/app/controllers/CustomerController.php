<?php
declare(strict_types=1);

namespace App\Controllers;

use App\lib\Auth;
use App\lib\CSRF;
use App\lib\Database;
use App\lib\Security;
use App\lib\Validator;
use App\models\ActivityLog;
use App\models\Category;
use App\models\Notification;
use App\models\User;
use App\models\Vehicle;

final class CustomerController
{
    private function requireAuth(): array
    {
        Auth::requireLogin();
        $user = Auth::user();
        if (!$user) {
            redirect(base_url('/login'));
        }
        return [
            'id' => $user->id,
            'role' => $user->role,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'status' => $user->status,
            'notification_count' => Notification::unreadCount((int)$user->id),
        ];
    }

    public function dashboard(): void
    {
        $user = $this->requireAuth();
        $pageTitle = 'Dashboard Overview';
        $pdo = Database::pdo();
        $userId = (int)$user['id'];
        $liveVehicles = Vehicle::recentActive(4);
        if (empty($liveVehicles)) {
            $liveVehicles = Vehicle::allWithCover(4);
        }
        $categories = Category::active();
        $notifications = Notification::recentForUser($userId, 5);
        $recentWishlist = $pdo->prepare(
            "SELECT w.created_at, v.id AS vehicle_id, v.brand, v.model, v.year, v.price, v.currency,
                    COALESCE(
                        (SELECT vi.image_url FROM vehicle_images vi WHERE vi.vehicle_id = v.id ORDER BY vi.sort_order ASC, vi.id ASC LIMIT 1),
                        'assets/img/vehicle-luxury.svg'
                    ) AS cover
             FROM wishlists w
             INNER JOIN vehicles v ON v.id = w.vehicle_id
             WHERE w.user_id = ?
             ORDER BY w.created_at DESC
             LIMIT 4"
        );
        $recentWishlist->execute([$userId]);
        $recentWishlistItems = $recentWishlist->fetchAll();
        $countQuery = static function (string $sql) use ($pdo, $userId): int {
            $st = $pdo->prepare($sql);
            $st->execute([$userId]);
            return (int)($st->fetchColumn() ?: 0);
        };

        $dashboardStats = [
            'wishlist_items' => $countQuery('SELECT COUNT(*) FROM wishlists WHERE user_id = ?'),
            'active_bookings' => $countQuery("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status IN ('pending','confirmed')"),
            'test_drives' => $countQuery('SELECT COUNT(*) FROM test_drive_requests WHERE user_id = ?'),
            'notifications' => $countQuery('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0'),
        ];
        $activityFeed = array_map(static function (array $row): array {
            return [
                'label' => ucfirst(str_replace('_', ' ', (string)$row['type'])),
                'title' => (string)$row['title'],
                'body' => (string)($row['body'] ?? ''),
                'created_at' => (string)$row['created_at'],
                'is_read' => (int)($row['is_read'] ?? 0),
            ];
        }, $notifications);

        require __DIR__ . '/../views/layouts/dashboard_layout.php';
    }

    public function wishlist(): void
    {
        $user = $this->requireAuth();
        $pageTitle = 'My Wishlist';
        $pdo = Database::pdo();
        $userId = (int)$user['id'];
        $itemsStmt = $pdo->prepare(
            "SELECT
                w.id AS wishlist_id,
                w.created_at,
                v.id AS vehicle_id,
                v.brand,
                v.model,
                v.year,
                v.price,
                v.currency,
                v.status,
                v.featured,
                COALESCE(c.name, 'Uncategorized') AS category_name,
                COALESCE(
                    (SELECT vi.image_url
                     FROM vehicle_images vi
                     WHERE vi.vehicle_id = v.id
                     ORDER BY vi.sort_order ASC, vi.id ASC
                     LIMIT 1),
                    'assets/img/vehicle-luxury.svg'
                ) AS cover
             FROM wishlists w
             INNER JOIN vehicles v ON v.id = w.vehicle_id
             LEFT JOIN categories c ON c.id = v.category_id
             WHERE w.user_id = ?
             ORDER BY w.created_at DESC"
        );
        $itemsStmt->execute([$userId]);
        $wishlistItems = $itemsStmt->fetchAll();
        $wishlistCount = count($wishlistItems);
        $wishlistValue = array_reduce($wishlistItems, static fn(float $carry, array $item): float => $carry + (float)($item['price'] ?? 0), 0.0);
        $contentView = __DIR__ . '/../views/customer/wishlist_content.php';
        require __DIR__ . '/../views/layouts/dashboard_layout.php';
    }

    public function bookings(): void
    {
        $user = $this->requireAuth();
        $pageTitle = 'My Bookings';
        $pdo = Database::pdo();
        $userId = (int)$user['id'];
        $q = trim((string)($_GET['q'] ?? ''));
        $statusFilter = trim((string)($_GET['status'] ?? 'all'));
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $where = ' WHERE b.user_id = ?';
        $params = [$userId];
        if ($q !== '') {
            $where .= ' AND (v.brand LIKE ? OR v.model LIKE ? OR b.booking_type LIKE ? OR b.contact_name LIKE ?)';
            $like = '%' . $q . '%';
            array_push($params, $like, $like, $like, $like);
        }
        if (in_array($statusFilter, ['pending', 'confirmed', 'cancelled', 'completed'], true)) {
            $where .= ' AND b.status = ?';
            $params[] = $statusFilter;
        }

        $countStmt = $pdo->prepare(
            'SELECT COUNT(*)
             FROM bookings b
             INNER JOIN vehicles v ON v.id = b.vehicle_id' . $where
        );
        $countStmt->execute($params);
        $total = (int)($countStmt->fetchColumn() ?: 0);
        $totalPages = max(1, (int)ceil($total / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $bookingsStmt = $pdo->prepare(
            "SELECT
                b.id,
                b.booking_type,
                b.status,
                b.contact_name,
                b.contact_email,
                b.contact_phone,
                b.notes,
                b.created_at,
                b.updated_at,
                v.id AS vehicle_id,
                v.brand,
                v.model,
                v.year,
                v.price,
                v.currency
             FROM bookings b
             INNER JOIN vehicles v ON v.id = b.vehicle_id" . $where . "
             ORDER BY b.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );
        $bookingsStmt->execute($params);
        $bookings = $bookingsStmt->fetchAll();

        $pendingBookings = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status = 'pending'");
        $pendingBookings->execute([$userId]);
        $confirmedBookings = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status = 'confirmed'");
        $confirmedBookings->execute([$userId]);
        $completedBookings = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status = 'completed'");
        $completedBookings->execute([$userId]);
        $stats = [
            'pending' => (int)($pendingBookings->fetchColumn() ?: 0),
            'confirmed' => (int)($confirmedBookings->fetchColumn() ?: 0),
            'completed' => (int)($completedBookings->fetchColumn() ?: 0),
        ];
        $filters = compact('q', 'statusFilter', 'page', 'perPage', 'total', 'totalPages');

        $contentView = __DIR__ . '/../views/customer/bookings_content.php';
        require __DIR__ . '/../views/layouts/dashboard_layout.php';
    }

    public function requests(): void
    {
        $user = $this->requireAuth();
        $pageTitle = 'Test Drive Requests';
        $pdo = Database::pdo();
        $userId = (int)$user['id'];
        $q = trim((string)($_GET['q'] ?? ''));
        $statusFilter = trim((string)($_GET['status'] ?? 'all'));
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $where = ' WHERE t.user_id = ?';
        $params = [$userId];
        if ($q !== '') {
            $where .= ' AND (v.brand LIKE ? OR v.model LIKE ? OR t.name LIKE ?)';
            $like = '%' . $q . '%';
            array_push($params, $like, $like, $like);
        }
        if (in_array($statusFilter, ['pending', 'confirmed', 'cancelled', 'completed'], true)) {
            $where .= ' AND t.status = ?';
            $params[] = $statusFilter;
        }

        $countStmt = $pdo->prepare(
            'SELECT COUNT(*)
             FROM test_drive_requests t
             INNER JOIN vehicles v ON v.id = t.vehicle_id' . $where
        );
        $countStmt->execute($params);
        $total = (int)($countStmt->fetchColumn() ?: 0);
        $totalPages = max(1, (int)ceil($total / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $requestsStmt = $pdo->prepare(
            "SELECT
                t.id,
                t.name,
                t.email,
                t.phone,
                t.preferred_datetime,
                t.status,
                t.created_at,
                v.id AS vehicle_id,
                v.brand,
                v.model,
                v.year,
                v.currency,
                v.price
             FROM test_drive_requests t
             INNER JOIN vehicles v ON v.id = t.vehicle_id" . $where . "
             ORDER BY t.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );
        $requestsStmt->execute($params);
        $requests = $requestsStmt->fetchAll();

        $pendingRequests = $pdo->prepare("SELECT COUNT(*) FROM test_drive_requests WHERE user_id = ? AND status = 'pending'");
        $pendingRequests->execute([$userId]);
        $confirmedRequests = $pdo->prepare("SELECT COUNT(*) FROM test_drive_requests WHERE user_id = ? AND status = 'confirmed'");
        $confirmedRequests->execute([$userId]);
        $completedRequests = $pdo->prepare("SELECT COUNT(*) FROM test_drive_requests WHERE user_id = ? AND status = 'completed'");
        $completedRequests->execute([$userId]);

        $stats = [
            'pending' => (int)($pendingRequests->fetchColumn() ?: 0),
            'confirmed' => (int)($confirmedRequests->fetchColumn() ?: 0),
            'completed' => (int)($completedRequests->fetchColumn() ?: 0),
        ];
        $filters = compact('q', 'statusFilter', 'page', 'perPage', 'total', 'totalPages');

        $contentView = __DIR__ . '/../views/customer/requests_content.php';
        require __DIR__ . '/../views/layouts/dashboard_layout.php';
    }

    public function notifications(): void
    {
        $user = $this->requireAuth();
        $pageTitle = 'Notifications';
        $pdo = Database::pdo();
        $userId = (int)$user['id'];

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            try {
                CSRF::verify($_POST['csrf_token'] ?? null);
                $notificationId = (int)($_POST['notification_id'] ?? 0);
                $action = trim((string)($_POST['action'] ?? ''));
                if ($action === 'mark_all_read') {
                    Notification::markAllRead($userId);
                } elseif ($notificationId > 0) {
                    Notification::markRead($notificationId, $userId);
                }
            } catch (\Throwable $e) {
                // Keep the page functional even if an action fails.
            }
            redirect(base_url('/notifications'));
        }

        $notifications = Notification::allForUser($userId, 50);
        $contentView = __DIR__ . '/../views/customer/notifications_content.php';
        require __DIR__ . '/../views/layouts/dashboard_layout.php';
    }

    public function bookingAction(): void
    {
        $user = $this->requireAuth();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect(base_url('/bookings'));
        }

        try {
            CSRF::verify($_POST['csrf_token'] ?? null);
            $bookingId = (int)($_POST['booking_id'] ?? 0);
            $action = trim((string)($_POST['action'] ?? ''));
            $userId = (int)$user['id'];
            $pdo = Database::pdo();

            $st = $pdo->prepare('SELECT * FROM bookings WHERE id = ? AND user_id = ? LIMIT 1');
            $st->execute([$bookingId, $userId]);
            $booking = $st->fetch();
            if (!$booking) {
                throw new \InvalidArgumentException('Booking not found');
            }

            if ($action === 'cancel') {
                $update = $pdo->prepare('UPDATE bookings SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
                $update->execute(['cancelled', $bookingId]);
            } elseif ($action === 'reschedule') {
                $requested = trim((string)($_POST['reschedule_time'] ?? ''));
                if ($requested === '') {
                    throw new \InvalidArgumentException('Please select a new time');
                }
                $requestedDisplay = $this->formatDateTimeForNote($requested);
                $existingNotes = trim((string)($booking['notes'] ?? ''));
                $notes = trim($existingNotes . "\nReschedule requested for: " . $requestedDisplay);
                $update = $pdo->prepare('UPDATE bookings SET status = ?, notes = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
                $update->execute(['pending', $notes, $bookingId]);
            } else {
                throw new \InvalidArgumentException('Invalid booking action');
            }
        } catch (\Throwable $e) {
            redirect(base_url('/bookings?error=1'));
        }

        redirect(base_url('/bookings?updated=1'));
    }

    public function requestAction(): void
    {
        $user = $this->requireAuth();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect(base_url('/requests'));
        }

        try {
            CSRF::verify($_POST['csrf_token'] ?? null);
            $requestId = (int)($_POST['request_id'] ?? 0);
            $action = trim((string)($_POST['action'] ?? ''));
            $userId = (int)$user['id'];
            $pdo = Database::pdo();

            $st = $pdo->prepare('SELECT * FROM test_drive_requests WHERE id = ? AND user_id = ? LIMIT 1');
            $st->execute([$requestId, $userId]);
            $request = $st->fetch();
            if (!$request) {
                throw new \InvalidArgumentException('Test drive request not found');
            }

            if ($action === 'cancel') {
                $update = $pdo->prepare('UPDATE test_drive_requests SET status = ? WHERE id = ?');
                $update->execute(['cancelled', $requestId]);
            } elseif ($action === 'reschedule') {
                $preferred = trim((string)($_POST['preferred_datetime'] ?? ''));
                if ($preferred === '') {
                    throw new \InvalidArgumentException('Please select a new time');
                }
                $dt = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $preferred) ?: \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $preferred);
                if (!$dt) {
                    throw new \InvalidArgumentException('Invalid date and time');
                }
                $update = $pdo->prepare('UPDATE test_drive_requests SET preferred_datetime = ?, status = ? WHERE id = ?');
                $update->execute([$dt->format('Y-m-d H:i:s'), 'pending', $requestId]);
            } else {
                throw new \InvalidArgumentException('Invalid test drive action');
            }
        } catch (\Throwable $e) {
            redirect(base_url('/requests?error=1'));
        }

        redirect(base_url('/requests?updated=1'));
    }

    private function formatDateTimeForNote(string $value): string
    {
        $dt = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $value) ?: \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value);
        if ($dt) {
            return $dt->format('Y-m-d H:i');
        }
        return $value;
    }

    public function profile(): void
    {
        $user = $this->requireAuth();
        $pageTitle = 'My Profile';
        $account = Auth::user();
        $successMessage = null;
        $errors = [];

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            try {
                CSRF::verify($_POST['csrf_token'] ?? null);
                $action = trim((string)($_POST['action'] ?? 'update_profile'));

                if ($action === 'update_password') {
                    $currentPassword = (string)($_POST['current_password'] ?? '');
                    $newPassword = (string)($_POST['new_password'] ?? '');
                    $confirmPassword = (string)($_POST['confirm_password'] ?? '');
                    if (!$account || !$account->verifyPassword($currentPassword)) {
                        throw new \InvalidArgumentException('Current password is incorrect');
                    }
                    Validator::password($newPassword);
                    if ($newPassword !== $confirmPassword) {
                        throw new \InvalidArgumentException('Passwords do not match');
                    }
                    User::updatePassword((int)$account->id, $newPassword);
                    $successMessage = 'Password updated successfully.';
                } else {
                    $fullName = Security::sanitize((string)($_POST['full_name'] ?? ''));
                    $email = Security::sanitize((string)($_POST['email'] ?? ''));
                    $phone = Security::sanitize((string)($_POST['phone'] ?? ''));
                    Validator::required($fullName, 'Full name');
                    Validator::required($email, 'Email');
                    Validator::email($email, 'Email');
                    if ($account && User::findByEmailExcept($email, (int)$account->id)) {
                        throw new \InvalidArgumentException('That email address is already in use');
                    }
                    User::updateProfile((int)$account->id, $fullName, $email, $phone !== '' ? $phone : null);
                    $successMessage = 'Profile updated successfully.';
                }

                ActivityLog::record((int)($account->id ?? 0), 'profile_updated', 'user', (int)($account->id ?? 0));
                $account = Auth::user();
                $user = $this->requireAuth();
            } catch (\Throwable $e) {
                $errors[] = $e->getMessage();
            }
        }

        $contentView = __DIR__ . '/../views/customer/profile_content.php';
        require __DIR__ . '/../views/layouts/dashboard_layout.php';
    }
}


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
use App\models\Setting;
use App\models\User;
use App\models\Vehicle;

final class AdminController
{
    private function renderModule(string $pageTitle, string $moduleTitle, string $moduleDescription, string $viewFile, array $data = []): void
    {
        Auth::requireAdmin();

        $moduleTitle = $moduleTitle;
        $moduleDescription = $moduleDescription;
        extract($data, EXTR_SKIP);
        $contentView = __DIR__ . '/../views/admin/' . ltrim($viewFile, '/');
        require __DIR__ . '/../views/layouts/admin_layout.php';
    }

    public function dashboard(): void
    {
        Auth::requireAdmin();
        $pageTitle = 'Admin Dashboard';
        $admin = Auth::user();
        $adminId = $admin ? (int)$admin->id : 0;
        $counts = Vehicle::counts();
        $categoryCounts = Category::counts();
        $pdo = Database::pdo();
        $recentActivities = ActivityLog::recent(8);
        $recentNotifications = $adminId > 0 ? Notification::recentForUser($adminId, 6) : [];
        $recentBookings = $pdo->query(
            "SELECT
                b.contact_name,
                COALESCE(v.brand, 'Unknown') AS brand,
                COALESCE(v.model, '') AS model,
                b.status,
                b.created_at,
                b.booking_type,
                b.contact_email
             FROM bookings b
             LEFT JOIN vehicles v ON v.id = b.vehicle_id
             ORDER BY b.created_at DESC
             LIMIT 5"
        )->fetchAll();
        $recentUsers = $pdo->query(
            "SELECT
                full_name,
                email,
                created_at,
                status
             FROM users
             ORDER BY created_at DESC
             LIMIT 5"
        )->fetchAll();
        $dashboardStats = [
            'total_vehicles' => $counts['total_vehicles'],
            'active_vehicles' => $counts['active_vehicles'],
            'featured_vehicles' => $counts['featured_vehicles'],
            'available_vehicles' => $counts['available_vehicles'],
            'total_categories' => $categoryCounts['total_categories'],
            'total_users' => (int)($pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() ?: 0),
            'total_bookings' => (int)($pdo->query('SELECT COUNT(*) FROM bookings')->fetchColumn() ?: 0),
            'total_sales' => (float)($pdo->query('SELECT COALESCE(SUM(sale_amount), 0) FROM sales')->fetchColumn() ?: 0),
            'unread_notifications' => $adminId > 0 ? Notification::unreadCount($adminId) : 0,
        ];
        $dashboardCategories = $pdo->query(
            "SELECT
                COALESCE(c.name, 'Uncategorized') AS name,
                COUNT(v.id) AS total
             FROM categories c
             LEFT JOIN vehicles v ON v.category_id = c.id
             GROUP BY c.id, c.name
             ORDER BY total DESC, name ASC
             LIMIT 5"
        )->fetchAll();
        $contentView = __DIR__ . '/../views/admin/dashboard.php';
        $recentBookings = $recentBookings;
        $recentUsers = $recentUsers;
        $dashboardCategories = $dashboardCategories;
        $recentActivities = $recentActivities;
        $recentNotifications = $recentNotifications;
        require __DIR__ . '/../views/layouts/admin_layout.php';
    }

    public function vehicles(): void
    {
        Auth::requireAdmin();

        $pageTitle = 'Vehicle Management';
        $categories = Vehicle::categories();
        $counts = Vehicle::counts();
        $vehicles = [];
        $editVehicle = null;
        $editVehicleId = (int)($_GET['edit'] ?? 0);
        $successMessage = null;
        $errors = [];
        $old = $this->blankVehicleForm();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            try {
                CSRF::verify($_POST['csrf_token'] ?? null);

                $deleteId = (int)($_POST['delete_id'] ?? 0);
                if ($deleteId > 0) {
                    Vehicle::delete($deleteId);
                    $this->recordAdminActivity('vehicle_deleted', 'vehicle', $deleteId, ['source' => 'admin_vehicle_form']);
                    $this->notifyAdminUsers(
                        'Vehicle deleted',
                        'A vehicle card was removed from the showroom inventory.',
                        'system'
                    );
                    redirect(base_url('/admin/vehicles?deleted=1'));
                }

                $vehicleId = (int)($_POST['vehicle_id'] ?? 0);
                $old = $this->mapVehicleForm($_POST);

                $payload = $this->normalizeVehiclePayload($old, $categories);
                $imageUrls = $this->collectVehicleImages($_FILES['vehicle_images'] ?? null, $old['image_url']);

                if ($vehicleId > 0) {
                    if ($imageUrls === []) {
                        $imageUrls = null;
                    }
                    Vehicle::update($vehicleId, $payload, $imageUrls);
                    $this->recordAdminActivity('vehicle_updated', 'vehicle', $vehicleId, [
                        'brand' => $payload['brand'],
                        'model' => $payload['model'],
                        'status' => $payload['status'],
                    ]);
                    $this->notifyAdminUsers(
                        'Vehicle updated',
                        trim($payload['brand'] . ' ' . $payload['model']) . ' was updated in the showroom inventory.',
                        'system'
                    );
                    redirect(base_url('/admin/vehicles?updated=1&id=' . $vehicleId));
                }

                if ($imageUrls === []) {
                    $imageUrls = ['assets/img/vehicle-luxury.svg'];
                }

                $newVehicleId = Vehicle::create($payload, $imageUrls);
                $this->recordAdminActivity('vehicle_created', 'vehicle', $newVehicleId, [
                    'brand' => $payload['brand'],
                    'model' => $payload['model'],
                    'status' => $payload['status'],
                    'featured' => $payload['featured'],
                ]);
                $this->notifyAdminUsers(
                    'Vehicle created',
                    trim($payload['brand'] . ' ' . $payload['model']) . ' was added to the showroom inventory.',
                    'system'
                );
                redirect(base_url('/admin/vehicles?created=1&id=' . $newVehicleId));
            } catch (\Throwable $e) {
                http_response_code(422);
                $errors[] = $e->getMessage();
                $editVehicleId = (int)($_POST['vehicle_id'] ?? $editVehicleId);
            }
        }

        if (!empty($_GET['created'])) {
            $successMessage = 'Vehicle card created successfully.';
        } elseif (!empty($_GET['updated'])) {
            $successMessage = 'Vehicle card updated successfully.';
        } elseif (!empty($_GET['deleted'])) {
            $successMessage = 'Vehicle card deleted successfully.';
        }

        if ($editVehicleId > 0) {
            $editVehicle = Vehicle::findById($editVehicleId);
            if (!$editVehicle) {
                $editVehicleId = 0;
            } elseif (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || empty($errors)) {
                $old = $this->vehicleToForm($editVehicle);
            }
        }

        $vehicles = Vehicle::allWithCover(24);
        $contentView = __DIR__ . '/../views/admin/vehicles.php';
        require __DIR__ . '/../views/layouts/admin_layout.php';
    }

    public function categories(): void
    {
        Auth::requireAdmin();

        $pageTitle = 'Categories Management';
        $categories = Category::all();
        $counts = Category::counts();
        $successMessage = null;
        $errors = [];
        $editCategory = null;
        $editCategoryId = (int)($_GET['edit'] ?? 0);
        $old = ['category_id' => '', 'name' => '', 'slug' => ''];

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            try {
                CSRF::verify($_POST['csrf_token'] ?? null);

                $deleteId = (int)($_POST['delete_id'] ?? 0);
                if ($deleteId > 0) {
                    $deletedCategory = Category::findById($deleteId);
                    Category::delete($deleteId);
                    $this->recordAdminActivity('category_deleted', 'category', $deleteId, [
                        'name' => (string)($deletedCategory['name'] ?? ''),
                        'slug' => (string)($deletedCategory['slug'] ?? ''),
                    ]);
                    $this->notifyAdminUsers(
                        'Category deleted',
                        trim((string)($deletedCategory['name'] ?? 'A category')) . ' was deleted from the showroom taxonomy.',
                        'system'
                    );
                    redirect(base_url('/admin/categories?deleted=1'));
                }

                $categoryId = (int)($_POST['category_id'] ?? 0);
                $old = [
                    'category_id' => (string)$categoryId,
                    'name' => Security::sanitize($_POST['name'] ?? ''),
                    'slug' => Security::sanitize($_POST['slug'] ?? ''),
                ];

                Validator::required($old['name'], 'Category name');
                $slug = $old['slug'] !== '' ? $old['slug'] : $old['name'];

                if ($categoryId > 0) {
                    Category::update($categoryId, $old['name'], $slug);
                    $this->recordAdminActivity('category_updated', 'category', $categoryId, [
                        'name' => $old['name'],
                        'slug' => $slug,
                    ]);
                    $this->notifyAdminUsers(
                        'Category updated',
                        $old['name'] . ' category was updated in the system.',
                        'system'
                    );
                    redirect(base_url('/admin/categories?updated=1&id=' . $categoryId));
                }

                $newId = Category::create($old['name'], $slug);
                $this->recordAdminActivity('category_created', 'category', $newId, [
                    'name' => $old['name'],
                    'slug' => $slug,
                ]);
                $this->notifyAdminUsers(
                    'Category created',
                    $old['name'] . ' category was added to the system.',
                    'system'
                );
                redirect(base_url('/admin/categories?created=1&id=' . $newId));
            } catch (\Throwable $e) {
                http_response_code(422);
                $errors[] = $e->getMessage();
                $editCategoryId = (int)($_POST['category_id'] ?? $editCategoryId);
            }
        }

        if (!empty($_GET['created'])) {
            $successMessage = 'Category created successfully.';
        } elseif (!empty($_GET['updated'])) {
            $successMessage = 'Category updated successfully.';
        } elseif (!empty($_GET['deleted'])) {
            $successMessage = 'Category deleted successfully.';
        }

        if ($editCategoryId > 0) {
            $editCategory = Category::findById($editCategoryId);
            if ($editCategory) {
                if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || empty($errors)) {
                    $old = [
                        'category_id' => (string)$editCategory['id'],
                        'name' => (string)$editCategory['name'],
                        'slug' => (string)$editCategory['slug'],
                    ];
                }
            } else {
                $editCategoryId = 0;
            }
        }

        $this->renderModule(
            $pageTitle,
            'Categories Management',
            'Maintain vehicle categories and keep vehicle cards organized.',
            'categories.php',
            compact('categories', 'counts', 'successMessage', 'errors', 'old', 'editCategory')
        );
    }

    public function customers(): void
    {
        Auth::requireAdmin();
        $pageTitle = 'Customers Management';
        $pdo = Database::pdo();
        $customers = $pdo->query(
            "SELECT
                u.id,
                u.full_name,
                u.email,
                u.phone,
                u.status,
                u.created_at,
                (SELECT COUNT(*) FROM bookings b WHERE b.user_id = u.id) AS booking_count,
                (SELECT COUNT(*) FROM wishlists w WHERE w.user_id = u.id) AS wishlist_count,
                (SELECT COUNT(*) FROM vehicle_comparisons c WHERE c.user_id = u.id) AS compare_count
             FROM users u
             WHERE u.role = 'customer'
             ORDER BY u.created_at DESC"
        )->fetchAll();
        $stats = User::counts();

        $this->renderModule(
            $pageTitle,
            'Customers Management',
            'Review customer accounts, wishlist activity, and booking history.',
            'customers.php',
            compact('customers', 'stats')
        );
    }

    public function bookings(): void
    {
        Auth::requireAdmin();
        $pageTitle = 'Booking Management';
        $pdo = Database::pdo();
        $successMessage = null;
        $errors = [];
        $q = trim((string)($_GET['q'] ?? ''));
        $statusFilter = trim((string)($_GET['status'] ?? 'all'));
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 12;

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            try {
                CSRF::verify($_POST['csrf_token'] ?? null);
                $bookingId = (int)($_POST['booking_id'] ?? 0);
                $status = trim((string)($_POST['status'] ?? 'pending'));
                $returnQ = trim((string)($_POST['return_q'] ?? ''));
                $returnStatus = trim((string)($_POST['return_status'] ?? 'all'));
                $returnPage = trim((string)($_POST['return_page'] ?? '1'));
                if ($bookingId > 0) {
                    if (!in_array($status, ['pending', 'confirmed', 'cancelled', 'completed'], true)) {
                        throw new \InvalidArgumentException('Invalid booking status');
                    }
                    $st = $pdo->prepare('UPDATE bookings SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
                    $st->execute([$status, $bookingId]);
                    $redirectQuery = ['updated' => 1, 'id' => $bookingId];
                    if ($returnQ !== '') {
                        $redirectQuery['q'] = $returnQ;
                    }
                    if (in_array($returnStatus, ['pending', 'confirmed', 'cancelled', 'completed'], true)) {
                        $redirectQuery['status'] = $returnStatus;
                    }
                    if ((int)$returnPage > 1) {
                        $redirectQuery['page'] = (int)$returnPage;
                    }
                    redirect(base_url('/admin/bookings?' . http_build_query($redirectQuery)));
                }
            } catch (\Throwable $e) {
                http_response_code(422);
                $errors[] = $e->getMessage();
            }
        }

        if (!empty($_GET['updated'])) {
            $successMessage = 'Booking status updated successfully.';
        }

        $where = ' WHERE 1=1';
        $params = [];
        if ($q !== '') {
            $where .= ' AND (b.contact_name LIKE ? OR b.contact_email LIKE ? OR v.brand LIKE ? OR v.model LIKE ? OR u.full_name LIKE ?)';
            $like = '%' . $q . '%';
            array_push($params, $like, $like, $like, $like, $like);
        }
        if (in_array($statusFilter, ['pending', 'confirmed', 'cancelled', 'completed'], true)) {
            $where .= ' AND b.status = ?';
            $params[] = $statusFilter;
        }

        $countStmt = $pdo->prepare(
            'SELECT COUNT(*)
             FROM bookings b
             LEFT JOIN vehicles v ON v.id = b.vehicle_id
             LEFT JOIN users u ON u.id = b.user_id' . $where
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
                v.brand,
                v.model,
                v.year,
                COALESCE(u.full_name, 'Guest Customer') AS user_name
             FROM bookings b
             LEFT JOIN vehicles v ON v.id = b.vehicle_id
             LEFT JOIN users u ON u.id = b.user_id" . $where . "
             ORDER BY b.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );
        $bookingsStmt->execute($params);
        $bookings = $bookingsStmt->fetchAll();
        $filters = compact('q', 'statusFilter', 'page', 'perPage', 'total', 'totalPages');

        $this->renderModule(
            $pageTitle,
            'Booking Management',
            'Handle reservations, booking approvals, and customer follow-up.',
            'bookings.php',
            compact('bookings', 'successMessage', 'errors', 'filters')
        );
    }

    public function testDrives(): void
    {
        Auth::requireAdmin();
        $pageTitle = 'Test Drive Requests';
        $pdo = Database::pdo();
        $successMessage = null;
        $errors = [];
        $q = trim((string)($_GET['q'] ?? ''));
        $statusFilter = trim((string)($_GET['status'] ?? 'all'));
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 12;

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            try {
                CSRF::verify($_POST['csrf_token'] ?? null);
                $requestId = (int)($_POST['request_id'] ?? 0);
                $status = trim((string)($_POST['status'] ?? 'pending'));
                $returnQ = trim((string)($_POST['return_q'] ?? ''));
                $returnStatus = trim((string)($_POST['return_status'] ?? 'all'));
                $returnPage = trim((string)($_POST['return_page'] ?? '1'));
                if ($requestId > 0) {
                    if (!in_array($status, ['pending', 'confirmed', 'cancelled', 'completed'], true)) {
                        throw new \InvalidArgumentException('Invalid test drive status');
                    }
                    $st = $pdo->prepare('UPDATE test_drive_requests SET status = ? WHERE id = ?');
                    $st->execute([$status, $requestId]);
                    $redirectQuery = ['updated' => 1, 'id' => $requestId];
                    if ($returnQ !== '') {
                        $redirectQuery['q'] = $returnQ;
                    }
                    if (in_array($returnStatus, ['pending', 'confirmed', 'cancelled', 'completed'], true)) {
                        $redirectQuery['status'] = $returnStatus;
                    }
                    if ((int)$returnPage > 1) {
                        $redirectQuery['page'] = (int)$returnPage;
                    }
                    redirect(base_url('/admin/test-drives?' . http_build_query($redirectQuery)));
                }
            } catch (\Throwable $e) {
                http_response_code(422);
                $errors[] = $e->getMessage();
            }
        }

        if (!empty($_GET['updated'])) {
            $successMessage = 'Test drive status updated successfully.';
        }

        $where = ' WHERE 1=1';
        $params = [];
        if ($q !== '') {
            $where .= ' AND (t.name LIKE ? OR t.email LIKE ? OR v.brand LIKE ? OR v.model LIKE ?)';
            $like = '%' . $q . '%';
            array_push($params, $like, $like, $like, $like);
        }
        if (in_array($statusFilter, ['pending', 'confirmed', 'cancelled', 'completed'], true)) {
            $where .= ' AND t.status = ?';
            $params[] = $statusFilter;
        }

        $countStmt = $pdo->prepare(
            'SELECT COUNT(*)
             FROM test_drive_requests t
             LEFT JOIN vehicles v ON v.id = t.vehicle_id' . $where
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
                v.brand,
                v.model,
                v.year
             FROM test_drive_requests t
             LEFT JOIN vehicles v ON v.id = t.vehicle_id" . $where . "
             ORDER BY t.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );
        $requestsStmt->execute($params);
        $requests = $requestsStmt->fetchAll();
        $filters = compact('q', 'statusFilter', 'page', 'perPage', 'total', 'totalPages');

        $this->renderModule(
            $pageTitle,
            'Test Drive Requests',
            'Manage incoming test drive requests and schedule confirmations.',
            'test_drives.php',
            compact('requests', 'successMessage', 'errors', 'filters')
        );
    }

    public function inventory(): void
    {
        Auth::requireAdmin();
        $pageTitle = 'Inventory Management';
        $pdo = Database::pdo();
        $successMessage = null;
        $errors = [];

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            try {
                CSRF::verify($_POST['csrf_token'] ?? null);
                $inventoryId = (int)($_POST['inventory_id'] ?? 0);
                $stockQty = (int)($_POST['stock_qty'] ?? 0);
                if ($inventoryId > 0) {
                    if ($stockQty < 0) {
                        throw new \InvalidArgumentException('Stock quantity cannot be negative');
                    }
                    $st = $pdo->prepare('UPDATE inventory_items SET stock_qty = ? WHERE id = ?');
                    $st->execute([$stockQty, $inventoryId]);
                    redirect(base_url('/admin/inventory?updated=1&id=' . $inventoryId));
                }
            } catch (\Throwable $e) {
                http_response_code(422);
                $errors[] = $e->getMessage();
            }
        }

        if (!empty($_GET['updated'])) {
            $successMessage = 'Inventory updated successfully.';
        }

        $inventory = $pdo->query(
            "SELECT
                i.id,
                i.stock_qty,
                i.created_at,
                v.id AS vehicle_id,
                v.brand,
                v.model,
                v.year,
                v.currency,
                v.price,
                COALESCE(c.name, 'Uncategorized') AS category_name
             FROM inventory_items i
             INNER JOIN vehicles v ON v.id = i.vehicle_id
             LEFT JOIN categories c ON c.id = v.category_id
             ORDER BY i.created_at DESC"
        )->fetchAll();

        $this->renderModule(
            $pageTitle,
            'Inventory Management',
            'Track stock levels, availability, and showroom allocation.',
            'inventory.php',
            compact('inventory', 'successMessage', 'errors')
        );
    }

    public function sales(): void
    {
        Auth::requireAdmin();
        $pageTitle = 'Sales Management';
        $pdo = Database::pdo();
        $sales = $pdo->query(
            "SELECT
                s.id,
                s.sale_amount,
                s.currency,
                s.sold_at,
                s.created_at,
                COALESCE(u.full_name, 'Guest Customer') AS customer_name,
                v.brand,
                v.model,
                v.year
             FROM sales s
             LEFT JOIN users u ON u.id = s.user_id
             LEFT JOIN vehicles v ON v.id = s.vehicle_id
             ORDER BY s.sold_at DESC"
        )->fetchAll();

        $metrics = $pdo->query(
            "SELECT
                COUNT(*) AS total_sales,
                COALESCE(SUM(sale_amount), 0) AS revenue,
                COALESCE(AVG(sale_amount), 0) AS average_sale
             FROM sales"
        )->fetch() ?: [];

        $this->renderModule(
            $pageTitle,
            'Sales Management',
            'Monitor deal progress, sales totals, and revenue performance.',
            'sales.php',
            [
                'sales' => $sales,
                'metrics' => $metrics,
            ]
        );
    }

    public function reports(): void
    {
        Auth::requireAdmin();
        $pageTitle = 'Reports';
        $reportData = $this->buildReportsData();

        $this->renderModule(
            $pageTitle,
            'Reports',
            'Review daily, monthly, and yearly sales reports with export tools.',
            'reports.php',
            $reportData
        );
    }

    public function users(): void
    {
        Auth::requireAdmin();
        $pageTitle = 'User Management';
        $pdo = Database::pdo();
        $successMessage = null;
        $errors = [];

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            try {
                CSRF::verify($_POST['csrf_token'] ?? null);
                $userId = (int)($_POST['user_id'] ?? 0);
                $status = trim((string)($_POST['status'] ?? 'active'));
                if ($userId > 0) {
                    if ($userId === (int)(Auth::id() ?? 0)) {
                        throw new \InvalidArgumentException('You cannot disable your own active admin session here.');
                    }
                    User::updateStatus($userId, $status);
                    redirect(base_url('/admin/users?updated=1&id=' . $userId));
                }
            } catch (\Throwable $e) {
                http_response_code(422);
                $errors[] = $e->getMessage();
            }
        }

        if (!empty($_GET['updated'])) {
            $successMessage = 'User account updated successfully.';
        }

        $users = User::all();
        $stats = User::counts();

        $this->renderModule(
            $pageTitle,
            'User Management',
            'Manage admin access, customer accounts, and platform roles.',
            'users.php',
            compact('users', 'stats', 'successMessage', 'errors')
        );
    }

    public function activityLogs(): void
    {
        Auth::requireAdmin();
        $pageTitle = 'Activity Logs';
        $logs = ActivityLog::recent(100);

        $this->renderModule(
            $pageTitle,
            'Activity Logs',
            'Review login, logout, vehicle, category, and settings changes in the admin audit trail.',
            'activity_logs.php',
            compact('logs')
        );
    }

    public function notifications(): void
    {
        Auth::requireAdmin();
        $pageTitle = 'Admin Notifications';
        $admin = Auth::user();
        $adminId = $admin ? (int)$admin->id : 0;
        $successMessage = null;
        $errors = [];

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            try {
                CSRF::verify($_POST['csrf_token'] ?? null);
                $notificationId = (int)($_POST['notification_id'] ?? 0);
                $action = trim((string)($_POST['action'] ?? ''));
                if ($action === 'mark_all_read') {
                    Notification::markAllRead($adminId);
                } elseif ($notificationId > 0) {
                    Notification::markRead($notificationId, $adminId);
                }
                redirect(base_url('/admin/notifications?updated=1'));
            } catch (\Throwable $e) {
                http_response_code(422);
                $errors[] = $e->getMessage();
            }
        }

        if (!empty($_GET['updated'])) {
            $successMessage = 'Notification status updated successfully.';
        }

        $notifications = $adminId > 0 ? Notification::allForUser($adminId, 50) : [];
        $unreadCount = $adminId > 0 ? Notification::unreadCount($adminId) : 0;

        $this->renderModule(
            $pageTitle,
            'Admin Notifications',
            'Track unread admin alerts, system notices, and dealership updates.',
            'notifications.php',
            compact('notifications', 'unreadCount', 'successMessage', 'errors')
        );
    }

    public function settings(): void
    {
        Auth::requireAdmin();
        $pageTitle = 'Settings';
        $successMessage = null;
        $errors = [];
        $settings = $this->currentSettings();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            try {
                CSRF::verify($_POST['csrf_token'] ?? null);

                $settings = $this->settingsFromRequest($_POST, $_FILES['logo_file'] ?? null, $settings);
                Setting::setMany($settings);
                $this->recordAdminActivity('settings_updated', 'settings', null, [
                    'keys' => array_keys($settings),
                ]);
                $this->notifyAdminUsers(
                    'Settings updated',
                    'Platform settings were saved successfully.',
                    'system'
                );
                redirect(base_url('/admin/settings?saved=1'));
            } catch (\Throwable $e) {
                http_response_code(422);
                $errors[] = $e->getMessage();
                $settings = array_merge($settings, [
                    'system_name' => trim(Security::sanitize((string)($_POST['system_name'] ?? $settings['system_name']))),
                    'contact_email' => trim(Security::sanitize((string)($_POST['contact_email'] ?? $settings['contact_email']))),
                    'contact_phone' => trim(Security::sanitize((string)($_POST['contact_phone'] ?? $settings['contact_phone']))),
                    'contact_address' => trim(Security::sanitize((string)($_POST['contact_address'] ?? $settings['contact_address']))),
                    'footer_text' => trim(Security::sanitize((string)($_POST['footer_text'] ?? $settings['footer_text']))),
                    'homepage_title' => trim(Security::sanitize((string)($_POST['homepage_title'] ?? $settings['homepage_title']))),
                    'homepage_subtitle' => trim(Security::sanitize((string)($_POST['homepage_subtitle'] ?? $settings['homepage_subtitle']))),
                    'homepage_about' => trim(Security::sanitize((string)($_POST['homepage_about'] ?? $settings['homepage_about']))),
                ]);
            }
        }

        if (!empty($_GET['saved'])) {
            $successMessage = 'Settings saved successfully.';
        }

        $this->renderModule(
            $pageTitle,
            'Settings',
            'Configure dealer branding, notifications, and platform preferences.',
            'settings.php',
            compact('settings', 'successMessage', 'errors')
        );
    }

    public function vehicleReport(): void
    {
        Auth::requireAdmin();
        $this->renderModule(
            'Vehicle Report',
            'Reports',
            'Review the full showroom inventory with current stock and pricing.',
            'reports.php',
            array_merge($this->buildReportsData(), ['focusSection' => 'vehicles'])
        );
    }

    public function categoryReport(): void
    {
        Auth::requireAdmin();
        $this->renderModule(
            'Category Report',
            'Reports',
            'Review vehicle categories and how many vehicles are grouped under each one.',
            'reports.php',
            array_merge($this->buildReportsData(), ['focusSection' => 'categories'])
        );
    }

    public function reportPrint(): void
    {
        Auth::requireAdmin();
        $reportType = $this->resolveReportType((string)($_GET['report'] ?? 'all'));
        $reportData = $this->buildReportsData();
        $pageTitle = 'Printable Report';
        $focusSection = $reportType;
        require __DIR__ . '/../views/admin/reports_print.php';
    }

    public function reportPdf(): void
    {
        Auth::requireAdmin();
        $reportType = $this->resolveReportType((string)($_GET['report'] ?? 'all'));
        $reportData = $this->buildReportsData();
        $filename = 'automates-report-' . $reportType . '-' . date('Ymd_His') . '.pdf';
        $pdf = $this->buildPlainTextPdf(
            'AUTOMATES ' . strtoupper($reportType) . ' REPORT',
            $this->reportLines($reportType, $reportData)
        );

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
        exit;
    }

    public function reportExcel(): void
    {
        Auth::requireAdmin();
        $reportType = $this->resolveReportType((string)($_GET['report'] ?? 'all'));
        $reportData = $this->buildReportsData();
        $filename = 'automates-report-' . $reportType . '-' . date('Ymd_His') . '.xls';

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $this->buildExcelReport($reportType, $reportData);
        exit;
    }

    public function profile(): void
    {
        Auth::requireAdmin();
        $pageTitle = 'Admin Profile';
        $admin = Auth::user();

        $this->renderModule(
            $pageTitle,
            'Admin Profile',
            'Update the signed-in administrator profile and security options.',
            'profile.php',
            compact('admin')
        );
    }

    private function currentSettings(): array
    {
        return Setting::allMerged();
    }

    private function settingsFromRequest(array $input, ?array $logoFile, array $currentSettings = [], bool $allowLogoUpload = true): array
    {
        $base = array_merge(Setting::defaults(), $currentSettings);
        $systemName = trim(Security::sanitize((string)($input['system_name'] ?? $base['system_name'])));
        Validator::required($systemName, 'System Name');

        $settings = [
            'system_name' => $systemName,
            'contact_email' => trim(Security::sanitize((string)($input['contact_email'] ?? $base['contact_email']))),
            'contact_phone' => trim(Security::sanitize((string)($input['contact_phone'] ?? $base['contact_phone']))),
            'contact_address' => trim(Security::sanitize((string)($input['contact_address'] ?? $base['contact_address']))),
            'footer_text' => trim(Security::sanitize((string)($input['footer_text'] ?? $base['footer_text']))),
            'homepage_title' => trim(Security::sanitize((string)($input['homepage_title'] ?? $base['homepage_title']))),
            'homepage_subtitle' => trim(Security::sanitize((string)($input['homepage_subtitle'] ?? $base['homepage_subtitle']))),
            'homepage_about' => trim(Security::sanitize((string)($input['homepage_about'] ?? $base['homepage_about']))),
            'logo_path' => (string)($base['logo_path'] ?? ''),
        ];

        if ($settings['contact_email'] !== '') {
            Validator::email((string)$settings['contact_email'], 'Contact Email');
        }

        if ($allowLogoUpload && is_array($logoFile)) {
            $settings['logo_path'] = $this->saveSettingsLogo($logoFile, $settings['logo_path']);
        }

        return $settings;
    }

    private function saveSettingsLogo(array $file, string $currentPath = ''): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return $currentPath;
        }

        $tmpName = (string)($file['tmp_name'] ?? '');
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            return $currentPath;
        }

        $imageInfo = @getimagesize($tmpName);
        if ($imageInfo === false || empty($imageInfo['mime'])) {
            throw new \InvalidArgumentException('Logo upload must be a valid image file');
        }

        $extension = $this->mimeToExtension((string)$imageInfo['mime']);
        if ($extension === null) {
            throw new \InvalidArgumentException('Unsupported logo image format');
        }

        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/settings';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
            throw new \RuntimeException('Unable to create settings upload directory');
        }

        $filename = sprintf('logo_%s_%s.%s', date('YmdHis'), bin2hex(random_bytes(4)), $extension);
        $destination = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($tmpName, $destination)) {
            throw new \RuntimeException('Unable to store the uploaded logo');
        }

        return 'uploads/settings/' . $filename;
    }

    private function buildReportsData(): array
    {
        $pdo = Database::pdo();
        $monthly = $pdo->query(
            "SELECT DATE_FORMAT(sold_at, '%Y-%m') AS month, COUNT(*) AS sales_count, COALESCE(SUM(sale_amount), 0) AS revenue
             FROM sales
             GROUP BY DATE_FORMAT(sold_at, '%Y-%m')
             ORDER BY month DESC
             LIMIT 12"
        )->fetchAll();
        $summary = $pdo->query(
            "SELECT
                COALESCE(SUM(CASE WHEN DATE(sold_at) = CURDATE() THEN sale_amount ELSE 0 END), 0) AS daily_sales,
                COALESCE(SUM(CASE WHEN YEAR(sold_at) = YEAR(CURDATE()) AND MONTH(sold_at) = MONTH(CURDATE()) THEN sale_amount ELSE 0 END), 0) AS monthly_sales,
                COALESCE(SUM(CASE WHEN YEAR(sold_at) = YEAR(CURDATE()) THEN sale_amount ELSE 0 END), 0) AS yearly_sales
             FROM sales"
        )->fetch() ?: [];
        $vehicles = $pdo->query(
            "SELECT
                v.id,
                v.brand,
                v.model,
                v.year,
                v.price,
                v.currency,
                v.status,
                v.featured,
                COALESCE(i.stock_qty, 1) AS stock_qty,
                COALESCE(c.name, 'Uncategorized') AS category_name
             FROM vehicles v
             LEFT JOIN categories c ON c.id = v.category_id
             LEFT JOIN inventory_items i ON i.vehicle_id = v.id
             ORDER BY v.created_at DESC"
        )->fetchAll();
        $categories = Category::all();

        return compact('monthly', 'summary', 'vehicles', 'categories');
    }

    private function resolveReportType(string $reportType): string
    {
        $allowed = ['all', 'sales', 'vehicles', 'categories'];
        $reportType = strtolower(trim($reportType));
        return in_array($reportType, $allowed, true) ? $reportType : 'all';
    }

    private function reportLines(string $reportType, array $reportData): array
    {
        $lines = [
            'Generated: ' . date('Y-m-d H:i:s'),
            '',
        ];

        if (in_array($reportType, ['all', 'sales'], true)) {
            $summary = $reportData['summary'] ?? [];
            $lines[] = 'Sales Summary';
            $lines[] = 'Daily: ' . number_format((float)($summary['daily_sales'] ?? 0), 2);
            $lines[] = 'Monthly: ' . number_format((float)($summary['monthly_sales'] ?? 0), 2);
            $lines[] = 'Yearly: ' . number_format((float)($summary['yearly_sales'] ?? 0), 2);
            $lines[] = '';
            $lines[] = 'Monthly Sales';
            foreach (($reportData['monthly'] ?? []) as $row) {
                $lines[] = sprintf(
                    '%s | Count: %s | Revenue: %s',
                    (string)($row['month'] ?? ''),
                    number_format((int)($row['sales_count'] ?? 0)),
                    number_format((float)($row['revenue'] ?? 0), 2)
                );
            }
            $lines[] = '';
        }

        if (in_array($reportType, ['all', 'vehicles'], true)) {
            $lines[] = 'Vehicle Report';
            foreach (($reportData['vehicles'] ?? []) as $row) {
                $vehicleTitle = trim((string)($row['brand'] ?? '') . ' ' . (string)($row['model'] ?? ''));
                $lines[] = sprintf(
                    '#%d | %s | %s | %s %s | Stock: %s | %s',
                    (int)($row['id'] ?? 0),
                    $vehicleTitle,
                    (string)($row['category_name'] ?? 'Uncategorized'),
                    (string)($row['currency'] ?? 'USD'),
                    number_format((float)($row['price'] ?? 0), 2),
                    number_format((int)($row['stock_qty'] ?? 1)),
                    (string)($row['status'] ?? 'active')
                );
            }
            $lines[] = '';
        }

        if (in_array($reportType, ['all', 'categories'], true)) {
            $lines[] = 'Category Report';
            foreach (($reportData['categories'] ?? []) as $row) {
                $lines[] = sprintf(
                    '#%d | %s | Slug: %s | Vehicles: %s',
                    (int)($row['id'] ?? 0),
                    (string)($row['name'] ?? ''),
                    (string)($row['slug'] ?? ''),
                    number_format((int)($row['vehicle_count'] ?? 0))
                );
            }
        }

        return $lines;
    }

    private function buildExcelReport(string $reportType, array $reportData): string
    {
        $html = [];
        $html[] = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>AUTOMATES Report</title></head><body style="font-family: Arial, sans-serif;">';
        $html[] = '<h2>AUTOMATES Report</h2>';
        $html[] = '<p>Generated: ' . e(date('Y-m-d H:i:s')) . '</p>';

        if (in_array($reportType, ['all', 'sales'], true)) {
            $summary = $reportData['summary'] ?? [];
            $html[] = '<h3>Sales Summary</h3>';
            $html[] = '<table border="1" cellpadding="6" cellspacing="0"><tr><th>Daily</th><th>Monthly</th><th>Yearly</th></tr>';
            $html[] = '<tr><td>' . number_format((float)($summary['daily_sales'] ?? 0), 2) . '</td><td>' . number_format((float)($summary['monthly_sales'] ?? 0), 2) . '</td><td>' . number_format((float)($summary['yearly_sales'] ?? 0), 2) . '</td></tr></table>';
            $html[] = '<h3>Monthly Sales</h3><table border="1" cellpadding="6" cellspacing="0"><tr><th>Month</th><th>Count</th><th>Revenue</th></tr>';
            foreach (($reportData['monthly'] ?? []) as $row) {
                $html[] = '<tr><td>' . e((string)($row['month'] ?? '')) . '</td><td>' . number_format((int)($row['sales_count'] ?? 0)) . '</td><td>' . number_format((float)($row['revenue'] ?? 0), 2) . '</td></tr>';
            }
            $html[] = '</table>';
        }

        if (in_array($reportType, ['all', 'vehicles'], true)) {
            $html[] = '<h3>Vehicle Report</h3><table border="1" cellpadding="6" cellspacing="0"><tr><th>ID</th><th>Vehicle</th><th>Category</th><th>Stock</th><th>Status</th><th>Price</th></tr>';
            foreach (($reportData['vehicles'] ?? []) as $row) {
                $html[] = '<tr><td>' . (int)($row['id'] ?? 0) . '</td><td>' . e(trim((string)($row['brand'] ?? '') . ' ' . (string)($row['model'] ?? ''))) . '</td><td>' . e((string)($row['category_name'] ?? 'Uncategorized')) . '</td><td>' . number_format((int)($row['stock_qty'] ?? 1)) . '</td><td>' . e((string)($row['status'] ?? 'active')) . '</td><td>' . e((string)($row['currency'] ?? 'USD')) . ' ' . number_format((float)($row['price'] ?? 0), 2) . '</td></tr>';
            }
            $html[] = '</table>';
        }

        if (in_array($reportType, ['all', 'categories'], true)) {
            $html[] = '<h3>Category Report</h3><table border="1" cellpadding="6" cellspacing="0"><tr><th>ID</th><th>Name</th><th>Slug</th><th>Vehicles</th></tr>';
            foreach (($reportData['categories'] ?? []) as $row) {
                $html[] = '<tr><td>' . (int)($row['id'] ?? 0) . '</td><td>' . e((string)($row['name'] ?? '')) . '</td><td>' . e((string)($row['slug'] ?? '')) . '</td><td>' . number_format((int)($row['vehicle_count'] ?? 0)) . '</td></tr>';
            }
            $html[] = '</table>';
        }

        $html[] = '</body></html>';
        return implode("\n", $html);
    }

    private function buildPlainTextPdf(string $title, array $lines): string
    {
        $lines = array_map(fn(string $line): string => $this->pdfSafeText($line), $lines);
        $headerLines = array_merge([$this->pdfSafeText($title), ''], $lines);
        $perPage = 42;
        $pages = array_chunk($headerLines, $perPage);
        $objects = [];
        $kids = [];
        $objectNumber = 4;

        foreach ($pages as $pageLines) {
            $pageObjectNumber = $objectNumber;
            $contentObjectNumber = $objectNumber + 1;
            $kids[] = $pageObjectNumber . ' 0 R';
            $objects[$pageObjectNumber] = sprintf(
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 3 0 R >> >> /Contents %d 0 R >>',
                $contentObjectNumber
            );

            $content = "BT\n/F1 11 Tf\n14 TL\n50 740 Td\n";
            foreach ($pageLines as $index => $line) {
                $content .= '(' . $this->pdfEscape($line) . ') Tj';
                $content .= $index === array_key_last($pageLines) ? "\n" : "\nT*\n";
            }
            $content .= "ET";
            $objects[$contentObjectNumber] = '<< /Length ' . strlen($content) . " >>\nstream\n" . $content . "\nendstream";
            $objectNumber += 2;
        }

        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . count($kids) . ' >>';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';

        ksort($objects);
        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $number => $body) {
            $offsets[$number] = strlen($pdf);
            $pdf .= $number . " 0 obj\n" . $body . "\nendobj\n";
        }

        $xrefStart = strlen($pdf);
        $count = max(array_keys($objects)) + 1;
        $pdf .= "xref\n0 {$count}\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i < $count; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i] ?? 0);
        }
        $pdf .= "trailer << /Size {$count} /Root 1 0 R >>\nstartxref\n{$xrefStart}\n%%EOF";

        return $pdf;
    }

    private function pdfSafeText(string $text): string
    {
        $text = preg_replace('/[^\x20-\x7E]/', '?', $text) ?? $text;
        return $text;
    }

    private function pdfEscape(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\(', '\)'], $text);
    }

    private function recordAdminActivity(string $action, ?string $entityType = null, ?int $entityId = null, array $meta = []): void
    {
        $actorId = Auth::id();
        ActivityLog::record($actorId !== null ? (int)$actorId : null, $action, $entityType, $entityId, $meta);
    }

    private function notifyAdminUsers(string $title, string $body, string $type = 'system'): void
    {
        $pdo = Database::pdo();
        $admins = $pdo->query("SELECT id FROM users WHERE role = 'admin' AND status = 'active'")->fetchAll();
        foreach ($admins as $admin) {
            Notification::create((int)$admin['id'], $title, $body, $type);
        }
    }

    private function blankVehicleForm(): array
    {
        return [
            'vehicle_id' => '',
            'category_id' => '',
            'brand' => '',
            'model' => '',
            'year' => '',
            'price' => '',
            'currency' => 'USD',
            'mileage_km' => '',
            'fuel_type' => 'petrol',
            'transmission' => 'automatic',
            'seating_capacity' => '',
            'horsepower' => '',
            'description' => '',
            'status' => 'active',
            'featured' => '1',
            'image_url' => '',
        ];
    }

    private function vehicleToForm(array $vehicle): array
    {
        $images = $vehicle['images'] ?? [];
        $cover = '';
        if (!empty($images[0]['image_url'])) {
            $cover = (string)$images[0]['image_url'];
        }

        return [
            'vehicle_id' => (string)($vehicle['id'] ?? ''),
            'category_id' => (string)($vehicle['category_id'] ?? ''),
            'brand' => (string)($vehicle['brand'] ?? ''),
            'model' => (string)($vehicle['model'] ?? ''),
            'year' => (string)($vehicle['year'] ?? ''),
            'price' => (string)($vehicle['price'] ?? ''),
            'currency' => (string)($vehicle['currency'] ?? 'USD'),
            'mileage_km' => (string)($vehicle['mileage_km'] ?? ''),
            'fuel_type' => (string)($vehicle['fuel_type'] ?? 'petrol'),
            'transmission' => (string)($vehicle['transmission'] ?? 'automatic'),
            'seating_capacity' => (string)($vehicle['seating_capacity'] ?? ''),
            'horsepower' => (string)($vehicle['horsepower'] ?? ''),
            'description' => (string)($vehicle['description'] ?? ''),
            'status' => (string)($vehicle['status'] ?? 'active'),
            'featured' => !empty($vehicle['featured']) ? '1' : '0',
            'image_url' => $cover,
        ];
    }

    private function mapVehicleForm(array $input): array
    {
        return [
            'vehicle_id' => trim((string)($input['vehicle_id'] ?? '')),
            'category_id' => trim((string)($input['category_id'] ?? '')),
            'brand' => Security::sanitize($input['brand'] ?? ''),
            'model' => Security::sanitize($input['model'] ?? ''),
            'year' => trim((string)($input['year'] ?? '')),
            'price' => trim((string)($input['price'] ?? '')),
            'currency' => strtoupper(trim((string)($input['currency'] ?? 'USD'))),
            'mileage_km' => trim((string)($input['mileage_km'] ?? '')),
            'fuel_type' => trim((string)($input['fuel_type'] ?? 'petrol')),
            'transmission' => trim((string)($input['transmission'] ?? 'automatic')),
            'seating_capacity' => trim((string)($input['seating_capacity'] ?? '')),
            'horsepower' => trim((string)($input['horsepower'] ?? '')),
            'description' => Security::sanitize($input['description'] ?? ''),
            'status' => trim((string)($input['status'] ?? 'active')),
            'featured' => !empty($input['featured']) ? '1' : '0',
            'image_url' => Security::sanitize($input['image_url'] ?? ''),
        ];
    }

    private function normalizeVehiclePayload(array $form, array $categories): array
    {
        Validator::required($form['brand'], 'Brand');
        Validator::required($form['model'], 'Model');
        Validator::required($form['year'], 'Year');
        Validator::required($form['price'], 'Price');

        Validator::intRange((int)$form['year'], 1990, 2100, 'Year');
        if (!is_numeric($form['price']) || (float)$form['price'] <= 0) {
            throw new \InvalidArgumentException('Price must be a positive number');
        }

        $categoryIds = array_map('intval', array_column($categories, 'id'));
        if ($form['category_id'] !== '' && !in_array((int)$form['category_id'], $categoryIds, true)) {
            throw new \InvalidArgumentException('Selected category does not exist');
        }

        return [
            'category_id' => $form['category_id'] !== '' ? (int)$form['category_id'] : null,
            'brand' => $form['brand'],
            'model' => $form['model'],
            'year' => (int)$form['year'],
            'price' => (float)$form['price'],
            'currency' => $form['currency'] !== '' ? $form['currency'] : 'USD',
            'mileage_km' => $form['mileage_km'] !== '' ? (int)$form['mileage_km'] : null,
            'fuel_type' => in_array($form['fuel_type'], ['petrol', 'diesel', 'hybrid', 'electric'], true) ? $form['fuel_type'] : 'petrol',
            'transmission' => in_array($form['transmission'], ['automatic', 'manual'], true) ? $form['transmission'] : 'automatic',
            'seating_capacity' => $form['seating_capacity'] !== '' ? (int)$form['seating_capacity'] : null,
            'horsepower' => $form['horsepower'] !== '' ? (int)$form['horsepower'] : null,
            'description' => $form['description'] !== '' ? $form['description'] : null,
            'status' => in_array($form['status'], ['active', 'inactive', 'sold'], true) ? $form['status'] : 'active',
            'featured' => $form['featured'] === '1' ? 1 : 0,
        ];
    }

    private function collectVehicleImages(?array $files, string $fallbackUrl = ''): array
    {
        $images = [];

        if (is_array($files) && !empty($files['name']) && is_array($files['name'])) {
            $uploadDir = dirname(__DIR__, 2) . '/public/uploads/vehicles';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $count = count($files['name']);
            for ($i = 0; $i < $count; $i++) {
                if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                    continue;
                }

                $tmpName = (string)($files['tmp_name'][$i] ?? '');
                if ($tmpName === '' || !is_uploaded_file($tmpName)) {
                    continue;
                }

                $info = @getimagesize($tmpName);
                if ($info === false || empty($info['mime'])) {
                    continue;
                }

                $mime = (string)$info['mime'];
                $extension = $this->mimeToExtension($mime);
                if ($extension === null) {
                    continue;
                }

                $filename = sprintf('vehicle_%s_%s.%s', date('YmdHis'), bin2hex(random_bytes(4)), $extension);
                $destination = $uploadDir . '/' . $filename;

                if (move_uploaded_file($tmpName, $destination)) {
                    $images[] = 'uploads/vehicles/' . $filename;
                }
            }
        }

        $fallbackUrl = trim($fallbackUrl);
        if ($fallbackUrl !== '') {
            $images[] = $fallbackUrl;
        }

        return array_values(array_filter(array_map('trim', $images), static fn(string $path): bool => $path !== ''));
    }

    private function mimeToExtension(string $mime): ?string
    {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            default => null,
        };
    }
}

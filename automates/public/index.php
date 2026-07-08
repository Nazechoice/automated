<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Routing\Router;

$router = new Router();

// Home
$router->get('/', [\App\Controllers\SiteController::class, 'home']);
$router->get('/vehicles', [\App\Controllers\SiteController::class, 'vehicles']);

// Auth
$router->get('/login', [\App\Controllers\AuthController::class, 'loginForm']);
$router->post('/login', [\App\Controllers\AuthController::class, 'login']);
$router->get('/register', [\App\Controllers\AuthController::class, 'registerForm']);
$router->post('/register', [\App\Controllers\AuthController::class, 'register']);
$router->post('/logout', [\App\Controllers\AuthController::class, 'logout']);

// Customer
$router->get('/dashboard', [\App\Controllers\CustomerController::class, 'dashboard']);
$router->get('/wishlist', [\App\Controllers\CustomerController::class, 'wishlist']);
$router->get('/bookings', [\App\Controllers\CustomerController::class, 'bookings']);
$router->post('/bookings/action', [\App\Controllers\CustomerController::class, 'bookingAction']);
$router->get('/requests', [\App\Controllers\CustomerController::class, 'requests']);
$router->post('/requests/action', [\App\Controllers\CustomerController::class, 'requestAction']);
$router->get('/profile', [\App\Controllers\CustomerController::class, 'profile']);
$router->get('/notifications', [\App\Controllers\CustomerController::class, 'notifications']);
$router->post('/notifications', [\App\Controllers\CustomerController::class, 'notifications']);

// Admin
$router->get('/admin', [\App\Controllers\AdminController::class, 'dashboard']);
$router->get('/admin/vehicles', [\App\Controllers\AdminController::class, 'vehicles']);
$router->post('/admin/vehicles', [\App\Controllers\AdminController::class, 'vehicles']);
$router->get('/admin/categories', [\App\Controllers\AdminController::class, 'categories']);
$router->post('/admin/categories', [\App\Controllers\AdminController::class, 'categories']);
$router->get('/admin/customers', [\App\Controllers\AdminController::class, 'customers']);
$router->get('/admin/bookings', [\App\Controllers\AdminController::class, 'bookings']);
$router->get('/admin/test-drives', [\App\Controllers\AdminController::class, 'testDrives']);
$router->post('/admin/test-drives', [\App\Controllers\AdminController::class, 'testDrives']);
$router->get('/admin/inventory', [\App\Controllers\AdminController::class, 'inventory']);
$router->get('/admin/sales', [\App\Controllers\AdminController::class, 'sales']);
$router->get('/admin/reports', [\App\Controllers\AdminController::class, 'reports']);
$router->get('/admin/reports/vehicles', [\App\Controllers\AdminController::class, 'vehicleReport']);
$router->get('/admin/reports/categories', [\App\Controllers\AdminController::class, 'categoryReport']);
$router->get('/admin/reports/print', [\App\Controllers\AdminController::class, 'reportPrint']);
$router->get('/admin/reports/export/pdf', [\App\Controllers\AdminController::class, 'reportPdf']);
$router->get('/admin/reports/export/excel', [\App\Controllers\AdminController::class, 'reportExcel']);
$router->get('/admin/users', [\App\Controllers\AdminController::class, 'users']);
$router->get('/admin/settings', [\App\Controllers\AdminController::class, 'settings']);
$router->post('/admin/settings', [\App\Controllers\AdminController::class, 'settings']);
$router->get('/admin/profile', [\App\Controllers\AdminController::class, 'profile']);
$router->get('/admin/activity-logs', [\App\Controllers\AdminController::class, 'activityLogs']);
$router->get('/admin/notifications', [\App\Controllers\AdminController::class, 'notifications']);
$router->post('/admin/notifications', [\App\Controllers\AdminController::class, 'notifications']);

// API endpoints (JSON)
$router->get('/api/vehicles/search', [\App\Controllers\Api\VehicleApiController::class, 'search']);
$router->post('/api/wishlist/toggle', [\App\Controllers\Api\WishlistApiController::class, 'toggle']);
$router->post('/api/compare/toggle', [\App\Controllers\Api\CompareApiController::class, 'toggle']);

// Public catalog
$router->get('/vehicle', [\App\Controllers\SiteController::class, 'vehicle']);
$router->post('/vehicle/book', [\App\Controllers\SiteController::class, 'bookVehicle']);
$router->post('/vehicle/test-drive', [\App\Controllers\SiteController::class, 'testDriveVehicle']);

$router->dispatch();


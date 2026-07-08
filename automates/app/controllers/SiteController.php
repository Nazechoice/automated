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
use App\models\Vehicle;

final class SiteController
{
    public function home(): void
    {
        $pageTitle = 'Automobiles Management System';
        $user = Auth::user();
        $isAuthenticated = $user !== null;
        $featuredVehicles = Vehicle::featured(6);
        if (empty($featuredVehicles)) {
            $featuredVehicles = Vehicle::recentActive(6);
        }
        $latestVehicles = Vehicle::recentActive(6);
        $categories = Category::active();
        $counts = Vehicle::counts();
        $categoryCounts = Category::counts();
        
        require __DIR__ . '/../views/home.php';
    }

    public function vehicles(): void
    {
        $pageTitle = 'Browse Vehicles | AUTOMATES';
        $user = Auth::user();
        $isAuthenticated = $user !== null;
        $categories = Category::active();
        $filters = [
            'q' => trim((string)($_GET['q'] ?? '')),
            'category' => trim((string)($_GET['category'] ?? '')),
            'fuel' => trim((string)($_GET['fuel'] ?? '')),
            'transmission' => trim((string)($_GET['transmission'] ?? '')),
            'min_price' => trim((string)($_GET['min_price'] ?? '')),
            'max_price' => trim((string)($_GET['max_price'] ?? '')),
        ];
        $vehicles = Vehicle::browse($filters, 48);

        require __DIR__ . '/../views/vehicles.php';
    }

    public function vehicle(): void
    {
        $vehicleId = (int)($_GET['id'] ?? 0);
        $vehicle = $vehicleId > 0 ? Vehicle::findById($vehicleId) : null;
        $user = Auth::user();
        $isAuthenticated = $user !== null;
        if ($vehicle && (string)($vehicle['status'] ?? 'active') !== 'active') {
            $vehicle = null;
        }
        $relatedVehicles = $vehicle ? Vehicle::related((int)$vehicle['id'], isset($vehicle['category_id']) ? (int)$vehicle['category_id'] : null, 4) : [];
        $pageTitle = $vehicle
            ? trim((string)$vehicle['brand'] . ' ' . (string)$vehicle['model']) . ' | Vehicle Details'
            : 'Vehicle Details';

        require __DIR__ . '/../views/vehicle_detail.php';
    }

    public function bookVehicle(): void
    {
        $vehicleId = (int)($_POST['vehicle_id'] ?? 0);
        try {
            Auth::requireLogin();
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
                redirect(base_url('/'));
            }

            CSRF::verify($_POST['csrf_token'] ?? null);

            $vehicle = $vehicleId > 0 ? Vehicle::findById($vehicleId) : null;
            if (!$vehicle || (string)($vehicle['status'] ?? 'active') !== 'active') {
                throw new \InvalidArgumentException('Vehicle not available');
            }

            $user = Auth::user();
            if (!$user) {
                redirect(base_url('/login'));
            }

            $contactName = trim((string)($_POST['contact_name'] ?? $user->full_name ?? ''));
            $contactEmail = trim((string)($_POST['contact_email'] ?? $user->email ?? ''));
            $contactPhone = trim((string)($_POST['contact_phone'] ?? $user->phone ?? ''));
            $notes = trim((string)($_POST['notes'] ?? ''));

            Validator::required($contactName, 'Contact name');
            Validator::required($contactEmail, 'Contact email');
            Validator::email($contactEmail, 'Contact email');

            $pdo = Database::pdo();
            $st = $pdo->prepare(
                'INSERT INTO bookings (user_id, vehicle_id, booking_type, contact_name, contact_email, contact_phone, status, notes)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $st->execute([
                (int)$user->id,
                $vehicleId,
                'booking',
                $contactName,
                $contactEmail,
                $contactPhone !== '' ? $contactPhone : null,
                'pending',
                $notes !== '' ? Security::sanitize($notes) : null,
            ]);

            Notification::create((int)$user->id, 'Booking received', trim((string)$vehicle['brand'] . ' ' . (string)$vehicle['model']) . ' booking request is pending review.', 'booking');
            ActivityLog::record((int)$user->id, 'booking_created', 'booking', (int)$pdo->lastInsertId(), ['vehicle_id' => $vehicleId]);

            redirect(base_url('/vehicle?id=' . $vehicleId . '&booked=1'));
        } catch (\Throwable $e) {
            redirect(base_url('/vehicle?id=' . $vehicleId . '&error=booking_failed'));
        }
    }

    public function testDriveVehicle(): void
    {
        $vehicleId = (int)($_POST['vehicle_id'] ?? 0);
        try {
            Auth::requireLogin();
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
                redirect(base_url('/'));
            }

            CSRF::verify($_POST['csrf_token'] ?? null);

            $vehicle = $vehicleId > 0 ? Vehicle::findById($vehicleId) : null;
            if (!$vehicle || (string)($vehicle['status'] ?? 'active') !== 'active') {
                throw new \InvalidArgumentException('Vehicle not available');
            }

            $user = Auth::user();
            if (!$user) {
                redirect(base_url('/login'));
            }

            $name = trim((string)($_POST['name'] ?? $user->full_name ?? ''));
            $email = trim((string)($_POST['email'] ?? $user->email ?? ''));
            $phone = trim((string)($_POST['phone'] ?? $user->phone ?? ''));
            $preferred = trim((string)($_POST['preferred_datetime'] ?? ''));
            Validator::required($name, 'Name');
            Validator::required($email, 'Email');
            Validator::email($email, 'Email');

            $preferredDateTime = null;
            if ($preferred !== '') {
                $dt = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $preferred) ?: \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $preferred);
                if ($dt) {
                    $preferredDateTime = $dt->format('Y-m-d H:i:s');
                }
            }

            $pdo = Database::pdo();
            $st = $pdo->prepare(
                'INSERT INTO test_drive_requests (user_id, vehicle_id, name, email, phone, preferred_datetime, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $st->execute([
                (int)$user->id,
                $vehicleId,
                $name,
                $email,
                $phone !== '' ? $phone : null,
                $preferredDateTime,
                'pending',
            ]);

            Notification::create((int)$user->id, 'Test drive request received', trim((string)$vehicle['brand'] . ' ' . (string)$vehicle['model']) . ' test drive request is pending review.', 'test_drive');
            ActivityLog::record((int)$user->id, 'test_drive_created', 'test_drive_request', (int)$pdo->lastInsertId(), ['vehicle_id' => $vehicleId]);

            redirect(base_url('/vehicle?id=' . $vehicleId . '&test_drive=1'));
        } catch (\Throwable $e) {
            redirect(base_url('/vehicle?id=' . $vehicleId . '&error=test_drive_failed'));
        }
    }

    public function contactDealer(): void
    {
        $vehicleId = (int)($_POST['vehicle_id'] ?? 0);
        try {
            Auth::requireLogin();
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
                redirect(base_url('/'));
            }
            CSRF::verify($_POST['csrf_token'] ?? null);

            $vehicle = $vehicleId > 0 ? Vehicle::findById($vehicleId) : null;
            if (!$vehicle) {
                throw new \InvalidArgumentException('Vehicle not available');
            }

            $user = Auth::user();
            if (!$user) {
                redirect(base_url('/login'));
            }

            $name = trim((string)($_POST['name'] ?? $user->full_name ?? ''));
            $email = trim((string)($_POST['email'] ?? $user->email ?? ''));
            $phone = trim((string)($_POST['phone'] ?? $user->phone ?? ''));
            $message = trim((string)($_POST['message'] ?? ''));

            Validator::required($name, 'Name');
            Validator::required($email, 'Email');
            Validator::email($email, 'Email');
            Validator::required($message, 'Message');

            $pdo = Database::pdo();
            $st = $pdo->prepare(
                'INSERT INTO dealer_contacts (user_id, vehicle_id, name, email, phone, message)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $st->execute([
                (int)$user->id,
                $vehicleId,
                $name,
                $email,
                $phone !== '' ? $phone : null,
                Security::sanitize($message),
            ]);

            Notification::create((int)$user->id, 'Message sent to dealer', trim((string)$vehicle['brand'] . ' ' . (string)$vehicle['model']) . ' inquiry has been submitted.', 'message');
            ActivityLog::record((int)$user->id, 'dealer_contact_created', 'dealer_contact', (int)$pdo->lastInsertId(), ['vehicle_id' => $vehicleId]);

            redirect(base_url('/vehicle?id=' . $vehicleId . '&contacted=1'));
        } catch (\Throwable $e) {
            redirect(base_url('/vehicle?id=' . $vehicleId . '&error=contact_failed'));
        }
    }
}


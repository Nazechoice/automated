<?php
$pageTitle = 'My Bookings';
$bookings = $bookings ?? [];
$stats = $stats ?? ['pending' => 0, 'confirmed' => 0, 'completed' => 0];
$filters = $filters ?? ['q' => '', 'statusFilter' => 'all', 'page' => 1, 'perPage' => 10, 'total' => 0, 'totalPages' => 1];
$statusClasses = [
    'pending' => 'badge-pending',
    'confirmed' => 'badge-confirmed',
    'completed' => 'badge-completed',
    'cancelled' => 'badge-cancelled',
];

function booking_query_url(array $filters, int $page): string {
    $params = [];
    if (($filters['q'] ?? '') !== '') {
        $params['q'] = $filters['q'];
    }
    if (($filters['statusFilter'] ?? 'all') !== 'all') {
        $params['status'] = $filters['statusFilter'];
    }
    if ($page > 1) {
        $params['page'] = $page;
    }
    return base_url('/bookings' . (empty($params) ? '' : '?' . http_build_query($params)));
}
?>

<div class="dashboard-header">
    <div>
        <h1><i class="bi bi-calendar-check"></i> My Bookings</h1>
        <p class="text-muted mt-2">Track your vehicle bookings and manage them from the live database.</p>
    </div>
    <div>
        <a href="<?php echo base_url('/vehicles'); ?>" class="btn btn-accent btn-sm">
            <i class="bi bi-plus"></i> New Booking
        </a>
    </div>
</div>

<?php if (!empty($_GET['updated'])): ?>
    <div class="alert alert-success border-0 glass-card">
        <i class="bi bi-check-circle-fill me-2"></i>Your booking has been updated.
    </div>
<?php endif; ?>

<?php if (!empty($_GET['error'])): ?>
    <div class="alert alert-danger border-0 glass-card">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>We could not complete that booking action.
    </div>
<?php endif; ?>

<div class="dashboard-stats">
    <div class="stat-box">
        <div class="stat-box-value"><?php echo number_format((int)$stats['pending']); ?></div>
        <div class="stat-box-label">Pending</div>
    </div>
    <div class="stat-box">
        <div class="stat-box-value"><?php echo number_format((int)$stats['confirmed']); ?></div>
        <div class="stat-box-label">Confirmed</div>
    </div>
    <div class="stat-box">
        <div class="stat-box-value"><?php echo number_format((int)$stats['completed']); ?></div>
        <div class="stat-box-label">Completed</div>
    </div>
</div>

<div class="glass-card mb-4">
    <form method="get" action="<?php echo base_url('/bookings'); ?>" class="row g-3 align-items-end">
        <div class="col-lg-6">
            <label class="form-label">Search</label>
            <input type="text" name="q" class="form-control glass-input" placeholder="Vehicle, booking type, or customer name" value="<?php echo e((string)$filters['q']); ?>">
        </div>
        <div class="col-lg-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select glass-input">
                <option value="all" <?php echo (($filters['statusFilter'] ?? 'all') === 'all' ? 'selected' : ''); ?>>All</option>
                <?php foreach (['pending' => 'Pending', 'confirmed' => 'Confirmed', 'cancelled' => 'Cancelled', 'completed' => 'Completed'] as $value => $label): ?>
                    <option value="<?php echo e($value); ?>" <?php echo (($filters['statusFilter'] ?? 'all') === $value ? 'selected' : ''); ?>>
                        <?php echo e($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-lg-3 d-flex gap-2">
            <button type="submit" class="btn btn-accent w-100">
                <i class="bi bi-search"></i> Filter
            </button>
            <a href="<?php echo base_url('/bookings'); ?>" class="btn btn-ghost w-100">Reset</a>
        </div>
    </form>
</div>

<div class="dashboard-table">
    <table class="table table-hover mb-0">
        <thead>
            <tr>
                <th>Booking ID</th>
                <th>Vehicle</th>
                <th>Type</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($bookings)): ?>
                <?php foreach ($bookings as $booking): ?>
                    <?php
                    $bookingRef = '#BK-' . str_pad((string)($booking['id'] ?? 0), 5, '0', STR_PAD_LEFT);
                    $vehicleTitle = trim((string)($booking['brand'] ?? '') . ' ' . (string)($booking['model'] ?? ''));
                    $vehicleUrl = base_url('/vehicle?id=' . (int)($booking['vehicle_id'] ?? 0));
                    $status = strtolower((string)($booking['status'] ?? 'pending'));
                    $statusClass = $statusClasses[$status] ?? 'badge-pending';
                    $isTerminal = in_array($status, ['cancelled', 'completed'], true);
                    ?>
                    <tr>
                        <td><strong><?php echo e($bookingRef); ?></strong></td>
                        <td>
                            <div class="fw-semibold"><?php echo e($vehicleTitle . ' ' . (string)($booking['year'] ?? '')); ?></div>
                            <div class="text-muted small"><?php echo e((string)($booking['currency'] ?? 'USD')); ?> <?php echo number_format((float)($booking['price'] ?? 0)); ?></div>
                        </td>
                        <td><?php echo e(ucfirst((string)($booking['booking_type'] ?? 'booking'))); ?></td>
                        <td><span class="badge badge-status <?php echo e($statusClass); ?>"><?php echo e(ucfirst($status)); ?></span></td>
                        <td><span class="text-muted"><?php echo e(date('Y-m-d', strtotime((string)($booking['created_at'] ?? 'now')))); ?></span></td>
                        <td>
                            <div class="d-flex flex-column gap-2">
                                <a href="<?php echo e($vehicleUrl); ?>" class="btn btn-sm action-btn-small btn-accent">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                <?php if (!$isTerminal): ?>
                                    <form method="post" action="<?php echo base_url('/bookings/action'); ?>" class="d-flex gap-2 flex-wrap">
                                        <?php csrf_field(); ?>
                                        <input type="hidden" name="booking_id" value="<?php echo (int)$booking['id']; ?>">
                                        <button type="submit" name="action" value="cancel" class="btn btn-sm action-btn-small btn-danger">
                                            <i class="bi bi-x-circle"></i> Cancel
                                        </button>
                                    </form>
                                    <form method="post" action="<?php echo base_url('/bookings/action'); ?>" class="d-flex gap-2 flex-wrap">
                                        <?php csrf_field(); ?>
                                        <input type="hidden" name="booking_id" value="<?php echo (int)$booking['id']; ?>">
                                        <input type="datetime-local" name="reschedule_time" class="form-control form-control-sm glass-input" style="min-width: 180px;">
                                        <button type="submit" name="action" value="reschedule" class="btn btn-sm btn-ghost">
                                            <i class="bi bi-calendar"></i> Reschedule
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        No bookings found yet. Browse vehicles and submit your first request.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ((int)($filters['totalPages'] ?? 1) > 1): ?>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-4">
        <div class="text-muted small">
            Showing page <?php echo (int)$filters['page']; ?> of <?php echo (int)$filters['totalPages']; ?>, <?php echo number_format((int)$filters['total']); ?> result(s)
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a class="btn btn-ghost btn-sm <?php echo ((int)$filters['page'] <= 1 ? 'disabled' : ''); ?>" href="<?php echo e(booking_query_url($filters, max(1, (int)$filters['page'] - 1))); ?>">Previous</a>
            <a class="btn btn-accent btn-sm <?php echo ((int)$filters['page'] >= (int)$filters['totalPages'] ? 'disabled' : ''); ?>" href="<?php echo e(booking_query_url($filters, min((int)$filters['totalPages'], (int)$filters['page'] + 1))); ?>">Next</a>
        </div>
    </div>
<?php endif; ?>

<div class="glass-card mt-4">
    <h5 style="margin-bottom: 16px;"><i class="bi bi-info-circle"></i> Booking Information</h5>
    <p class="text-muted" style="margin-bottom: 0;">
        Booking status changes made by admin will appear here immediately because this page reads from the live database on every load.
    </p>
</div>

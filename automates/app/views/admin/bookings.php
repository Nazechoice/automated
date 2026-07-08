<?php
$bookings = $bookings ?? [];
$successMessage = $successMessage ?? null;
$errors = $errors ?? [];
$filters = $filters ?? ['q' => '', 'statusFilter' => 'all', 'page' => 1, 'perPage' => 12, 'total' => 0, 'totalPages' => 1];
$statusClasses = [
    'pending' => 'badge-pending',
    'confirmed' => 'badge-confirmed',
    'completed' => 'badge-completed',
    'cancelled' => 'badge-cancelled',
];

function admin_booking_url(array $filters, int $page): string {
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
    return base_url('/admin/bookings' . (empty($params) ? '' : '?' . http_build_query($params)));
}
?>

<div class="admin-header">
    <div>
        <h1><i class="bi bi-calendar-check-fill"></i> Booking Management</h1>
        <p class="text-muted mt-2">Track bookings, update statuses, and manage customer requests.</p>
    </div>
    <a href="<?php echo base_url('/admin'); ?>" class="btn btn-accent">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
</div>

<?php if ($successMessage): ?>
    <div class="alert alert-success border-0 glass-card">
        <i class="bi bi-check-circle-fill me-2"></i><?php echo e($successMessage); ?>
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger border-0 glass-card">
        <ul class="mb-0 ps-3">
            <?php foreach ($errors as $error): ?>
                <li><?php echo e((string)$error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="glass-card mb-4">
    <form method="get" action="<?php echo base_url('/admin/bookings'); ?>" class="row g-3 align-items-end">
        <div class="col-lg-6">
            <label class="form-label">Search</label>
            <input type="text" name="q" class="form-control glass-input" placeholder="Customer, email, or vehicle" value="<?php echo e((string)$filters['q']); ?>">
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
            <a href="<?php echo base_url('/admin/bookings'); ?>" class="btn btn-ghost w-100">Reset</a>
        </div>
    </form>
</div>

<div class="admin-table">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Vehicle</th>
                <th>Type</th>
                <th>Status</th>
                <th>Contact</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($bookings)): ?>
                <?php foreach ($bookings as $booking): ?>
                    <?php
                    $currentStatus = strtolower((string)($booking['status'] ?? 'pending'));
                    $statusClass = $statusClasses[$currentStatus] ?? 'badge-pending';
                    ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?php echo e((string)($booking['user_name'] ?? $booking['contact_name'] ?? 'Guest')); ?></div>
                            <div class="text-muted small"><?php echo e((string)($booking['contact_email'] ?? '')); ?></div>
                        </td>
                        <td><?php echo e(trim((string)($booking['brand'] ?? '') . ' ' . (string)($booking['model'] ?? '')) ?: 'N/A'); ?></td>
                        <td><?php echo e(ucfirst((string)($booking['booking_type'] ?? 'booking'))); ?></td>
                        <td><span class="badge-status <?php echo e($statusClass); ?>"><?php echo e(ucfirst($currentStatus)); ?></span></td>
                        <td>
                            <div><?php echo e((string)($booking['contact_phone'] ?? '')); ?></div>
                            <div class="text-muted small"><?php echo e((string)($booking['created_at'] ?? '')); ?></div>
                        </td>
                        <td style="min-width: 240px;">
                            <form method="post" action="<?php echo base_url('/admin/bookings'); ?>" class="d-flex gap-2 align-items-center flex-wrap">
                                <?php csrf_field(); ?>
                                <input type="hidden" name="booking_id" value="<?php echo (int)$booking['id']; ?>">
                                <input type="hidden" name="return_q" value="<?php echo e((string)$filters['q']); ?>">
                                <input type="hidden" name="return_status" value="<?php echo e((string)$filters['statusFilter']); ?>">
                                <input type="hidden" name="return_page" value="<?php echo (int)$filters['page']; ?>">
                                <select name="status" class="form-select form-select-sm glass-input">
                                    <?php foreach (['pending' => 'Pending', 'confirmed' => 'Confirmed', 'cancelled' => 'Cancelled', 'completed' => 'Completed'] as $value => $label): ?>
                                        <option value="<?php echo e($value); ?>" <?php echo ($currentStatus === $value ? 'selected' : ''); ?>>
                                            <?php echo e($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-sm btn-accent">Save</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No bookings found yet.</td>
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
            <a class="btn btn-ghost btn-sm <?php echo ((int)$filters['page'] <= 1 ? 'disabled' : ''); ?>" href="<?php echo e(admin_booking_url($filters, max(1, (int)$filters['page'] - 1))); ?>">Previous</a>
            <a class="btn btn-accent btn-sm <?php echo ((int)$filters['page'] >= (int)$filters['totalPages'] ? 'disabled' : ''); ?>" href="<?php echo e(admin_booking_url($filters, min((int)$filters['totalPages'], (int)$filters['page'] + 1))); ?>">Next</a>
        </div>
    </div>
<?php endif; ?>

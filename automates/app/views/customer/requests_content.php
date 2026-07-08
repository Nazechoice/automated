<?php
$pageTitle = 'My Test Drive Requests';
$requests = $requests ?? [];
$stats = $stats ?? ['pending' => 0, 'confirmed' => 0, 'completed' => 0];
$filters = $filters ?? ['q' => '', 'statusFilter' => 'all', 'page' => 1, 'perPage' => 10, 'total' => 0, 'totalPages' => 1];
$statusClasses = [
    'pending' => 'badge-pending',
    'confirmed' => 'badge-confirmed',
    'completed' => 'badge-completed',
    'cancelled' => 'badge-cancelled',
];

function request_query_url(array $filters, int $page): string {
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
    return base_url('/requests' . (empty($params) ? '' : '?' . http_build_query($params)));
}
?>

<div class="dashboard-header">
    <div>
        <h1><i class="bi bi-file-earmark-text"></i> Test Drive Requests</h1>
        <p class="text-muted mt-2">View and manage your live test drive requests from the database.</p>
    </div>
    <div>
        <a href="<?php echo base_url('/vehicles'); ?>" class="btn btn-accent btn-sm">
            <i class="bi bi-plus"></i> Schedule Test Drive
        </a>
    </div>
</div>

<?php if (!empty($_GET['updated'])): ?>
    <div class="alert alert-success border-0 glass-card">
        <i class="bi bi-check-circle-fill me-2"></i>Your test drive request has been updated.
    </div>
<?php endif; ?>

<?php if (!empty($_GET['error'])): ?>
    <div class="alert alert-danger border-0 glass-card">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>We could not complete that test drive action.
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
    <form method="get" action="<?php echo base_url('/requests'); ?>" class="row g-3 align-items-end">
        <div class="col-lg-6">
            <label class="form-label">Search</label>
            <input type="text" name="q" class="form-control glass-input" placeholder="Vehicle or customer name" value="<?php echo e((string)$filters['q']); ?>">
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
            <a href="<?php echo base_url('/requests'); ?>" class="btn btn-ghost w-100">Reset</a>
        </div>
    </form>
</div>

<div class="dashboard-table">
    <table class="table table-hover mb-0">
        <thead>
            <tr>
                <th>Request ID</th>
                <th>Vehicle</th>
                <th>Preferred Time</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($requests)): ?>
                <?php foreach ($requests as $request): ?>
                    <?php
                    $requestRef = '#TD-' . str_pad((string)($request['id'] ?? 0), 5, '0', STR_PAD_LEFT);
                    $vehicleTitle = trim((string)($request['brand'] ?? '') . ' ' . (string)($request['model'] ?? ''));
                    $vehicleUrl = base_url('/vehicle?id=' . (int)($request['vehicle_id'] ?? 0));
                    $status = strtolower((string)($request['status'] ?? 'pending'));
                    $statusClass = $statusClasses[$status] ?? 'badge-pending';
                    $isTerminal = in_array($status, ['cancelled', 'completed'], true);
                    $preferredLocal = !empty($request['preferred_datetime'])
                        ? date('Y-m-d\TH:i', strtotime((string)$request['preferred_datetime']))
                        : '';
                    ?>
                    <tr>
                        <td><strong><?php echo e($requestRef); ?></strong></td>
                        <td>
                            <div class="fw-semibold"><?php echo e($vehicleTitle . ' ' . (string)($request['year'] ?? '')); ?></div>
                            <div class="text-muted small"><?php echo e((string)($request['currency'] ?? 'USD')); ?> <?php echo number_format((float)($request['price'] ?? 0)); ?></div>
                        </td>
                        <td><?php echo e(!empty($request['preferred_datetime']) ? date('Y-m-d H:i', strtotime((string)$request['preferred_datetime'])) : 'Anytime'); ?></td>
                        <td><span class="badge badge-status <?php echo e($statusClass); ?>"><?php echo e(ucfirst($status)); ?></span></td>
                        <td><span class="text-muted"><?php echo e(date('Y-m-d', strtotime((string)($request['created_at'] ?? 'now')))); ?></span></td>
                        <td>
                            <div class="d-flex flex-column gap-2">
                                <a href="<?php echo e($vehicleUrl); ?>" class="btn btn-sm action-btn-small btn-accent">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                <?php if (!$isTerminal): ?>
                                    <form method="post" action="<?php echo base_url('/requests/action'); ?>" class="d-flex gap-2 flex-wrap">
                                        <?php csrf_field(); ?>
                                        <input type="hidden" name="request_id" value="<?php echo (int)$request['id']; ?>">
                                        <button type="submit" name="action" value="cancel" class="btn btn-sm action-btn-small btn-danger">
                                            <i class="bi bi-x-circle"></i> Cancel
                                        </button>
                                    </form>
                                    <form method="post" action="<?php echo base_url('/requests/action'); ?>" class="d-flex gap-2 flex-wrap">
                                        <?php csrf_field(); ?>
                                        <input type="hidden" name="request_id" value="<?php echo (int)$request['id']; ?>">
                                        <input type="datetime-local" name="preferred_datetime" class="form-control form-control-sm glass-input" value="<?php echo e($preferredLocal); ?>" style="min-width: 180px;">
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
                        No test drive requests found yet. Open a vehicle detail page to send your first request.
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
            <a class="btn btn-ghost btn-sm <?php echo ((int)$filters['page'] <= 1 ? 'disabled' : ''); ?>" href="<?php echo e(request_query_url($filters, max(1, (int)$filters['page'] - 1))); ?>">Previous</a>
            <a class="btn btn-accent btn-sm <?php echo ((int)$filters['page'] >= (int)$filters['totalPages'] ? 'disabled' : ''); ?>" href="<?php echo e(request_query_url($filters, min((int)$filters['totalPages'], (int)$filters['page'] + 1))); ?>">Next</a>
        </div>
    </div>
<?php endif; ?>

<div class="glass-card mt-4">
    <h5 style="margin-bottom: 16px;"><i class="bi bi-lightbulb"></i> Test Drive Tips</h5>
    <ul style="color: var(--text-secondary); margin-bottom: 0; padding-left: 20px;">
        <li style="margin-bottom: 12px;">Choose a preferred time so the dealer can prepare the vehicle.</li>
        <li style="margin-bottom: 12px;">Use the vehicle detail page to review specs before you visit.</li>
        <li style="margin-bottom: 12px;">Bring a valid driver's license and proof of insurance.</li>
        <li>Ask our team any questions about the car during the visit.</li>
    </ul>
</div>

<?php
$requests = $requests ?? [];
$successMessage = $successMessage ?? null;
$errors = $errors ?? [];
?>

<div class="admin-header">
    <div>
        <h1><i class="bi bi-file-earmark-text-fill"></i> Test Drive Requests</h1>
        <p class="text-muted mt-2">Review scheduled test drives and customer contact details.</p>
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

<div class="admin-table">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Vehicle</th>
                <th>Preferred Time</th>
                <th>Status</th>
                <th>Created</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($requests)): ?>
                <?php foreach ($requests as $request): ?>
                    <?php $currentStatus = strtolower((string)($request['status'] ?? 'pending')); ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?php echo e((string)$request['name']); ?></div>
                            <div class="text-muted small"><?php echo e((string)$request['email']); ?></div>
                        </td>
                        <td><?php echo e(trim((string)($request['brand'] ?? '') . ' ' . (string)($request['model'] ?? '')) ?: 'N/A'); ?></td>
                        <td><?php echo e((string)($request['preferred_datetime'] ?? 'Flexible')); ?></td>
                        <td>
                            <span class="badge-status <?php echo e($currentStatus === 'confirmed' ? 'badge-confirmed' : ($currentStatus === 'completed' ? 'badge-completed' : ($currentStatus === 'cancelled' ? 'badge-cancelled' : 'badge-pending'))); ?>">
                                <?php echo e((string)$request['status']); ?>
                            </span>
                        </td>
                        <td><?php echo e((string)$request['created_at']); ?></td>
                        <td style="min-width: 220px;">
                            <form method="post" action="<?php echo base_url('/admin/test-drives'); ?>" class="d-flex gap-2 align-items-center">
                                <?php csrf_field(); ?>
                                <input type="hidden" name="request_id" value="<?php echo (int)$request['id']; ?>">
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
                    <td colspan="6" class="text-center text-muted py-4">No test drive requests found yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

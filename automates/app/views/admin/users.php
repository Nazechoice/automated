<?php
$users = $users ?? [];
$stats = $stats ?? ['total_users' => 0, 'admin_users' => 0, 'customer_users' => 0, 'active_users' => 0];
$successMessage = $successMessage ?? null;
$errors = $errors ?? [];
?>

<div class="admin-header">
    <div>
        <h1><i class="bi bi-person-gear"></i> User Management</h1>
        <p class="text-muted mt-2">Manage admin and customer accounts across the platform.</p>
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

<div class="admin-stats">
    <div class="admin-stat-card">
        <div class="admin-stat-label">All Users</div>
        <div class="admin-stat-value"><?php echo number_format((int)$stats['total_users']); ?></div>
        <div class="admin-stat-trend">System accounts</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Admins</div>
        <div class="admin-stat-value"><?php echo number_format((int)$stats['admin_users']); ?></div>
        <div class="admin-stat-trend">Administrator accounts</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Customers</div>
        <div class="admin-stat-value"><?php echo number_format((int)$stats['customer_users']); ?></div>
        <div class="admin-stat-trend">Customer accounts</div>
    </div>
</div>

<div class="admin-table">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Update</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="fw-semibold"><?php echo e((string)$user['full_name']); ?></td>
                        <td><?php echo e((string)$user['email']); ?></td>
                        <td><?php echo e(ucfirst((string)$user['role'])); ?></td>
                        <td><?php echo e((string)($user['phone'] ?? 'N/A')); ?></td>
                        <td><span class="badge-status <?php echo ($user['status'] === 'active' ? 'badge-completed' : 'badge-pending'); ?>"><?php echo e((string)$user['status']); ?></span></td>
                        <td style="min-width: 220px;">
                            <form method="post" action="<?php echo base_url('/admin/users'); ?>" class="d-flex gap-2 align-items-center">
                                <?php csrf_field(); ?>
                                <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
                                <select name="status" class="form-select form-select-sm glass-input">
                                    <?php foreach (['active' => 'Active', 'disabled' => 'Disabled'] as $value => $label): ?>
                                        <option value="<?php echo e($value); ?>" <?php echo (($user['status'] ?? 'active') === $value ? 'selected' : ''); ?>>
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
                    <td colspan="6" class="text-center text-muted py-4">No users found yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

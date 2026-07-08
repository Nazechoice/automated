<?php
$logs = $logs ?? [];

$labelForAction = static function (string $action): string {
    return match ($action) {
        'admin_login' => 'Admin Login',
        'user_login' => 'User Login',
        'logout' => 'Logout',
        'vehicle_created' => 'Vehicle Created',
        'vehicle_updated' => 'Vehicle Updated',
        'vehicle_deleted' => 'Vehicle Deleted',
        'category_created' => 'Category Created',
        'category_updated' => 'Category Updated',
        'category_deleted' => 'Category Deleted',
        'settings_updated' => 'Settings Updated',
        'profile_updated' => 'Profile Updated',
        default => ucwords(str_replace('_', ' ', $action)),
    };
};
?>

<div class="admin-header">
    <div>
        <h1><i class="bi bi-clock-history"></i> Activity Logs</h1>
        <p class="text-muted mt-2">A live audit trail of login, logout, inventory, category, and settings changes.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?php echo base_url('/admin/notifications'); ?>" class="btn btn-ghost btn-sm">Notifications</a>
        <a href="<?php echo base_url('/admin/settings'); ?>" class="btn btn-accent btn-sm">Settings</a>
    </div>
</div>

<div class="admin-stats">
    <div class="admin-stat-card">
        <div class="admin-stat-label">Loaded Entries</div>
        <div class="admin-stat-value"><?php echo number_format(count($logs)); ?></div>
        <div class="admin-stat-trend">Most recent audit rows</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Tracked Actions</div>
        <div class="admin-stat-value">Live</div>
        <div class="admin-stat-trend">From activity_logs table</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Visibility</div>
        <div class="admin-stat-value">Full</div>
        <div class="admin-stat-trend">Admin only audit trail</div>
    </div>
</div>

<div class="admin-table">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>Time</th>
                <th>Actor</th>
                <th>Action</th>
                <th>Entity</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($logs)): ?>
                <?php foreach ($logs as $log): ?>
                    <?php $meta = json_decode((string)($log['meta'] ?? ''), true) ?: []; ?>
                    <tr>
                        <td><?php echo e((string)($log['created_at'] ?? '')); ?></td>
                        <td>
                            <div class="fw-semibold"><?php echo e((string)($log['actor_name'] ?? 'System')); ?></div>
                            <div class="text-muted small"><?php echo e((string)($log['actor_role'] ?? 'system')); ?></div>
                        </td>
                        <td>
                            <span class="badge-status badge-confirmed"><?php echo e($labelForAction((string)($log['action'] ?? 'activity'))); ?></span>
                        </td>
                        <td>
                            <div class="fw-semibold"><?php echo e((string)($log['entity_type'] ?? 'system')); ?></div>
                            <div class="text-muted small">ID: <?php echo e((string)($log['entity_id'] ?? '')); ?></div>
                        </td>
                        <td style="max-width: 420px;">
                            <?php if (!empty($meta)): ?>
                                <pre class="mb-0 text-muted" style="white-space: pre-wrap; font-size: 12px;"><?php echo e(json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre>
                            <?php else: ?>
                                <span class="text-muted">No extra details</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">No activity has been recorded yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$notifications = $notifications ?? [];
$unreadCount = $unreadCount ?? 0;
$successMessage = $successMessage ?? null;
$errors = $errors ?? [];
?>

<div class="admin-header">
    <div>
        <h1><i class="bi bi-bell-fill"></i> Admin Notifications</h1>
        <p class="text-muted mt-2">Review alerts from vehicle changes, settings updates, and customer requests.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <form method="post" action="<?php echo base_url('/admin/notifications'); ?>" class="mb-0">
            <?php csrf_field(); ?>
            <input type="hidden" name="action" value="mark_all_read">
            <button type="submit" class="btn btn-accent btn-sm">Mark All Read</button>
        </form>
        <a href="<?php echo base_url('/admin/activity-logs'); ?>" class="btn btn-ghost btn-sm">Activity Logs</a>
    </div>
</div>

<?php if ($successMessage): ?>
    <div class="alert alert-success border-0 glass-card">
        <i class="bi bi-check-circle-fill me-2"></i><?php echo e($successMessage); ?>
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger border-0 glass-card">
        <strong class="d-block mb-2">Please fix the following:</strong>
        <ul class="mb-0 ps-3">
            <?php foreach ($errors as $error): ?>
                <li><?php echo e((string)$error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="admin-stats">
    <div class="admin-stat-card">
        <div class="admin-stat-label">Unread</div>
        <div class="admin-stat-value"><?php echo number_format((int)$unreadCount); ?></div>
        <div class="admin-stat-trend">Notifications waiting for review</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Total Loaded</div>
        <div class="admin-stat-value"><?php echo number_format(count($notifications)); ?></div>
        <div class="admin-stat-trend">Latest inbox entries</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Action Ready</div>
        <div class="admin-stat-value">Yes</div>
        <div class="admin-stat-trend">Mark individual items read</div>
    </div>
</div>

<div class="admin-table">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>Status</th>
                <th>Title</th>
                <th>Message</th>
                <th>Type</th>
                <th>Received</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($notifications)): ?>
                <?php foreach ($notifications as $notification): ?>
                    <tr>
                        <td>
                            <span class="badge-status <?php echo !empty($notification['is_read']) ? 'badge-completed' : 'badge-pending'; ?>">
                                <?php echo !empty($notification['is_read']) ? 'Read' : 'Unread'; ?>
                            </span>
                        </td>
                        <td class="fw-semibold"><?php echo e((string)$notification['title']); ?></td>
                        <td><?php echo e((string)($notification['body'] ?? '')); ?></td>
                        <td><?php echo e(ucfirst((string)($notification['type'] ?? 'system'))); ?></td>
                        <td><?php echo e((string)($notification['created_at'] ?? '')); ?></td>
                        <td>
                            <form method="post" action="<?php echo base_url('/admin/notifications'); ?>" class="mb-0">
                                <?php csrf_field(); ?>
                                <input type="hidden" name="notification_id" value="<?php echo (int)$notification['id']; ?>">
                                <button type="submit" class="btn btn-outline-light btn-sm action-btn-small">Mark Read</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No admin notifications yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$notifications = $notifications ?? [];
?>

<div class="dashboard-header">
    <div>
        <h1>Notifications <i class="bi bi-bell-fill"></i></h1>
        <p class="text-muted mt-2">Review booking, test drive, and account notifications.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <form method="post" action="<?php echo base_url('/notifications'); ?>" class="mb-0">
            <?php csrf_field(); ?>
            <input type="hidden" name="action" value="mark_all_read">
            <button type="submit" class="btn btn-accent btn-sm">Mark All Read</button>
        </form>
        <a href="<?php echo base_url('/dashboard'); ?>" class="btn btn-ghost btn-sm">Back to Dashboard</a>
    </div>
</div>

<div class="dashboard-table">
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
                            <form method="post" action="<?php echo base_url('/notifications'); ?>" class="mb-0">
                                <?php csrf_field(); ?>
                                <input type="hidden" name="notification_id" value="<?php echo (int)$notification['id']; ?>">
                                <button type="submit" class="btn btn-outline-light btn-sm action-btn-small">
                                    Mark Read
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No notifications yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

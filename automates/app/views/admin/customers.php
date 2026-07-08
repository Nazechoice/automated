<?php
$customers = $customers ?? [];
$stats = $stats ?? ['total_users' => 0, 'admin_users' => 0, 'customer_users' => 0, 'active_users' => 0];
?>

<div class="admin-header">
    <div>
        <h1><i class="bi bi-people-fill"></i> Customers Management</h1>
        <p class="text-muted mt-2">Review customer accounts, saved vehicles, and booking activity.</p>
    </div>
    <a href="<?php echo base_url('/admin'); ?>" class="btn btn-accent">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
</div>

<div class="admin-stats">
    <div class="admin-stat-card">
        <div class="admin-stat-label">Total Users</div>
        <div class="admin-stat-value"><?php echo number_format((int)$stats['total_users']); ?></div>
        <div class="admin-stat-trend">All platform accounts</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Customers</div>
        <div class="admin-stat-value"><?php echo number_format((int)$stats['customer_users']); ?></div>
        <div class="admin-stat-trend">End-user accounts</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Active</div>
        <div class="admin-stat-value"><?php echo number_format((int)$stats['active_users']); ?></div>
        <div class="admin-stat-trend">Currently enabled</div>
    </div>
</div>

<div class="admin-table">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Bookings</th>
                <th>Wishlist</th>
                <th>Compare</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($customers)): ?>
                <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td class="fw-semibold"><?php echo e((string)$customer['full_name']); ?></td>
                        <td><?php echo e((string)$customer['email']); ?></td>
                        <td><?php echo e((string)($customer['phone'] ?? 'N/A')); ?></td>
                        <td><?php echo number_format((int)($customer['booking_count'] ?? 0)); ?></td>
                        <td><?php echo number_format((int)($customer['wishlist_count'] ?? 0)); ?></td>
                        <td><?php echo number_format((int)($customer['compare_count'] ?? 0)); ?></td>
                        <td><span class="badge-status <?php echo ($customer['status'] === 'active' ? 'badge-completed' : 'badge-pending'); ?>"><?php echo e((string)$customer['status']); ?></span></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No customer accounts found yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

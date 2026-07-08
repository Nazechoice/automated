<?php
// Admin Dashboard Overview
$pageTitle = 'Admin Dashboard';
$dashboardStats = $dashboardStats ?? [
    'total_vehicles' => 0,
    'active_vehicles' => 0,
    'featured_vehicles' => 0,
    'total_users' => 0,
    'total_bookings' => 0,
    'total_sales' => 0.0,
];
$recentBookings = $recentBookings ?? [];
$recentUsers = $recentUsers ?? [];
$dashboardCategories = $dashboardCategories ?? [];
?>

<div class="admin-header">
    <div>
        <h1><i class="bi bi-speedometer2"></i> Dashboard</h1>
        <p class="text-muted mt-2">System overview and key metrics</p>
    </div>
    <div>
        <button class="btn btn-ghost btn-sm">
            <i class="bi bi-download"></i> Export Report
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="admin-stats">
    <div class="admin-stat-card">
        <div class="admin-stat-label">Total Vehicles</div>
        <div class="admin-stat-value"><?php echo number_format((int)$dashboardStats['total_vehicles']); ?></div>
        <div class="admin-stat-trend"><i class="bi bi-car-front-fill"></i> Live inventory count</div>
    </div>
    
    <div class="admin-stat-card">
        <div class="admin-stat-label">Total Revenue</div>
        <div class="admin-stat-value">$<?php echo number_format((float)$dashboardStats['total_sales'], 0); ?></div>
        <div class="admin-stat-trend"><i class="bi bi-cash-coin"></i> Sales recorded in system</div>
    </div>
    
    <div class="admin-stat-card">
        <div class="admin-stat-label">Active Bookings</div>
        <div class="admin-stat-value"><?php echo number_format((int)$dashboardStats['total_bookings']); ?></div>
        <div class="admin-stat-trend"><i class="bi bi-calendar-check"></i> Booking records</div>
    </div>
    
    <div class="admin-stat-card">
        <div class="admin-stat-label">Registered Users</div>
        <div class="admin-stat-value"><?php echo number_format((int)$dashboardStats['total_users']); ?></div>
        <div class="admin-stat-trend"><i class="bi bi-people-fill"></i> Users in the platform</div>
    </div>
</div>

<!-- Charts Row -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 32px;">
    <!-- Sales Chart -->
    <div class="admin-chart">
        <h5 style="margin-bottom: 20px;">Revenue Trend</h5>
        <canvas id="revenueChart" height="80"></canvas>
    </div>
    
    <!-- Category Distribution -->
    <div class="admin-chart">
        <h5 style="margin-bottom: 20px;">Vehicle Categories</h5>
        <canvas id="categoryChart"></canvas>
    </div>
</div>

<!-- Recent Activity Section -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 32px;">
    <!-- Recent Bookings -->
    <div class="admin-table">
        <div style="padding: 20px; border-bottom: 1px solid var(--border-color);">
            <h5 style="margin-bottom: 0;">Recent Bookings</h5>
        </div>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Vehicle</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody style="font-size: 13px;">
                <?php if (!empty($recentBookings)): ?>
                    <?php foreach ($recentBookings as $booking): ?>
                        <tr>
                            <td><?php echo e((string)($booking['contact_name'] ?? 'Guest')); ?></td>
                            <td><?php echo e(trim((string)($booking['brand'] ?? '') . ' ' . (string)($booking['model'] ?? '')) ?: 'N/A'); ?></td>
                            <td><?php echo e(ucfirst((string)($booking['booking_type'] ?? 'booking'))); ?></td>
                            <td><span class="badge badge-status badge-<?php echo e((string)($booking['status'] ?? 'pending')); ?>"><?php echo e((string)($booking['status'] ?? 'pending')); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">No bookings yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Recent Users -->
    <div class="admin-table">
        <div style="padding: 20px; border-bottom: 1px solid var(--border-color);">
            <h5 style="margin-bottom: 0;">New Users</h5>
        </div>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Joined</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody style="font-size: 13px;">
                <?php if (!empty($recentUsers)): ?>
                    <?php foreach ($recentUsers as $userRow): ?>
                        <tr>
                            <td><?php echo e((string)($userRow['full_name'] ?? 'User')); ?></td>
                            <td><?php echo e((string)($userRow['email'] ?? '')); ?></td>
                            <td><?php echo e((string)($userRow['created_at'] ?? '')); ?></td>
                            <td><span class="badge badge-status badge-<?php echo e((string)($userRow['status'] ?? 'active')); ?>"><?php echo e((string)($userRow['status'] ?? 'active')); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">No users yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Activity and Notifications -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 32px;">
    <div class="admin-table">
        <div style="padding: 20px; border-bottom: 1px solid var(--border-color);">
            <h5 style="margin-bottom: 0;">Recent Activities</h5>
        </div>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Action</th>
                    <th>Actor</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody style="font-size: 13px;">
                <?php if (!empty($recentActivities)): ?>
                    <?php foreach ($recentActivities as $activity): ?>
                        <?php $activityLabel = ucwords(str_replace('_', ' ', (string)($activity['action'] ?? 'activity'))); ?>
                        <tr>
                            <td><?php echo e($activityLabel); ?></td>
                            <td><?php echo e((string)($activity['actor_name'] ?? 'System')); ?></td>
                            <td><?php echo e((string)($activity['created_at'] ?? '')); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">No activity logs yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="admin-table">
        <div style="padding: 20px; border-bottom: 1px solid var(--border-color);">
            <h5 style="margin-bottom: 0;">Recent Notifications</h5>
        </div>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody style="font-size: 13px;">
                <?php if (!empty($recentNotifications)): ?>
                    <?php foreach ($recentNotifications as $notification): ?>
                        <tr>
                            <td><?php echo e((string)($notification['title'] ?? 'Notification')); ?></td>
                            <td><?php echo e(ucfirst((string)($notification['type'] ?? 'system'))); ?></td>
                            <td><span class="badge badge-status <?php echo !empty($notification['is_read']) ? 'badge-completed' : 'badge-pending'; ?>"><?php echo !empty($notification['is_read']) ? 'Read' : 'Unread'; ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">No notifications yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Quick Actions -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; margin-bottom: 32px;">
    <a href="<?php echo base_url('/admin/vehicles'); ?>" class="btn btn-primary btn-sm" style="width: 100%; text-align: center;">
        <i class="bi bi-plus-circle"></i> Add Vehicle
    </a>
    <a href="<?php echo base_url('/admin/customers'); ?>" class="btn btn-primary btn-sm" style="width: 100%; text-align: center;">
        <i class="bi bi-person-plus"></i> Manage Users
    </a>
    <a href="<?php echo base_url('/admin/reports'); ?>" class="btn btn-primary btn-sm" style="width: 100%; text-align: center;">
        <i class="bi bi-file-text"></i> Generate Report
    </a>
    <a href="<?php echo base_url('/admin/settings'); ?>" class="btn btn-primary btn-sm" style="width: 100%; text-align: center;">
        <i class="bi bi-sliders"></i> Settings
    </a>
</div>

<!-- System Status -->
<div class="glass-card">
    <h5 style="margin-bottom: 20px;"><i class="bi bi-info-circle"></i> System Status</h5>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
        <div>
            <p class="text-muted" style="margin-bottom: 8px; font-size: 12px; text-transform: uppercase;">Database</p>
            <div style="display: flex; align-items: center; gap: 8px;">
                <div style="width: 8px; height: 8px; background: var(--color-success); border-radius: 50%;"></div>
                <span>Connected</span>
            </div>
        </div>
        <div>
            <p class="text-muted" style="margin-bottom: 8px; font-size: 12px; text-transform: uppercase;">Server</p>
            <div style="display: flex; align-items: center; gap: 8px;">
                <div style="width: 8px; height: 8px; background: var(--color-success); border-radius: 50%;"></div>
                <span>Healthy</span>
            </div>
        </div>
        <div>
            <p class="text-muted" style="margin-bottom: 8px; font-size: 12px; text-transform: uppercase;">API</p>
            <div style="display: flex; align-items: center; gap: 8px;">
                <div style="width: 8px; height: 8px; background: var(--color-success); border-radius: 50%;"></div>
                <span>Operational</span>
            </div>
        </div>
    </div>
</div>

<script>
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Revenue ($)',
                data: [180000, 210000, 195000, 280000, 250000, 290000],
                borderColor: '#00d4ff',
                backgroundColor: 'rgba(0, 212, 255, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#00d4ff',
                pointBorderColor: '#1a3a52',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(255, 255, 255, 0.05)' },
                    ticks: { color: '#a8b5c3' }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#a8b5c3' }
                }
            }
        }
    });
    
    // Category Distribution Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    const categoryLabels = <?php echo json_encode(array_map(static fn(array $row): string => (string)$row['name'], $dashboardCategories), JSON_UNESCAPED_SLASHES); ?>;
    const categoryTotals = <?php echo json_encode(array_map(static fn(array $row): int => (int)$row['total'], $dashboardCategories), JSON_UNESCAPED_SLASHES); ?>;
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: categoryLabels.length ? categoryLabels : ['SUV', 'Sedan', 'Luxury', 'Sports', 'Electric'],
            datasets: [{
                data: categoryTotals.length ? categoryTotals : [80, 120, 150, 90, 84],
                backgroundColor: [
                    '#00d4ff',
                    '#2d5a7b',
                    '#1a3a52',
                    '#ffa500',
                    '#00c853'
                ],
                borderColor: '#0a0e27',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: '#a8b5c3', font: { size: 12 } }
                }
            }
        }
    });
</script>


<?php
// Dashboard Overview Page
$pageTitle = 'Dashboard Overview';
$user = $user ?? [];
$dashboardStats = $dashboardStats ?? [
    'wishlist_items' => 0,
    'active_bookings' => 0,
    'test_drives' => 0,
    'notifications' => 0,
];
$liveVehicles = $liveVehicles ?? [];
$categories = $categories ?? [];
?>

<div class="dashboard-header">
    <div>
        <h1>Welcome, <?php echo e($user['full_name'] ?? 'User'); ?>! <i class="bi bi-hand-thumbs-up-fill"></i></h1>
        <p class="text-muted mt-2">Here's your dashboard overview</p>
    </div>
    <div>
        <a href="<?php echo base_url('/'); ?>" class="btn btn-ghost">
            <i class="bi bi-house"></i> Browse Vehicles
        </a>
    </div>
</div>

<!-- Statistics -->
<div class="dashboard-stats">
    <div class="stat-box">
        <div class="stat-box-value"><?php echo number_format((int)$dashboardStats['wishlist_items']); ?></div>
        <div class="stat-box-label">Wishlist Items</div>
    </div>
    <div class="stat-box">
        <div class="stat-box-value"><?php echo number_format((int)$dashboardStats['active_bookings']); ?></div>
        <div class="stat-box-label">Active Booking</div>
    </div>
    <div class="stat-box">
        <div class="stat-box-value"><?php echo number_format((int)$dashboardStats['test_drives']); ?></div>
        <div class="stat-box-label">Test Drives</div>
    </div>
    <div class="stat-box">
        <div class="stat-box-value"><?php echo number_format((int)$dashboardStats['notifications']); ?></div>
        <div class="stat-box-label">Notifications</div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-8">
        <div class="glass-card" style="height: 100%;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h4 style="margin-bottom: 4px;">Live Vehicles</h4>
                    <p class="text-muted mb-0">Active cars added by the admin appear here automatically.</p>
                </div>
                <a href="<?php echo base_url('/'); ?>" class="btn btn-ghost btn-sm">
                    <i class="bi bi-arrow-up-right"></i> Open Home
                </a>
            </div>

            <div class="row g-3">
                <?php if (!empty($liveVehicles)): ?>
                    <?php foreach ($liveVehicles as $vehicle): ?>
                        <?php
                        $vehicleTitle = trim((string)($vehicle['brand'] ?? '') . ' ' . (string)($vehicle['model'] ?? ''));
                        $vehicleUrl = base_url('/vehicle?id=' . (int)($vehicle['id'] ?? 0));
                        $vehicleImage = base_url((string)($vehicle['cover'] ?? 'assets/img/vehicle-luxury.svg'));
                        ?>
                        <div class="col-md-6">
                            <div class="vehicle-card h-100">
                                <div class="vehicle-card-image">
                                    <img src="<?php echo e($vehicleImage); ?>" alt="<?php echo e($vehicleTitle); ?>">
                                    <span class="vehicle-card-badge"><?php echo !empty($vehicle['featured']) ? 'Featured' : 'Live'; ?></span>
                                </div>
                                <div class="vehicle-card-content">
                                    <h4><?php echo e($vehicleTitle . ' ' . (string)($vehicle['year'] ?? '')); ?></h4>
                                    <div class="vehicle-card-meta">
                                        <span><i class="bi bi-tag"></i> <?php echo e((string)($vehicle['category_name'] ?? 'Uncategorized')); ?></span>
                                        <span><i class="bi bi-speedometer"></i> <?php echo !empty($vehicle['mileage_km']) ? e(number_format((float)$vehicle['mileage_km']) . ' km') : 'Brand new'; ?></span>
                                    </div>
                                    <div class="vehicle-card-price"><?php echo e((string)($vehicle['currency'] ?? 'USD')); ?> <?php echo number_format((float)($vehicle['price'] ?? 0)); ?></div>
                                    <a href="<?php echo e($vehicleUrl); ?>" class="btn btn-primary btn-sm vehicle-card-btn">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="text-center text-muted py-4 border rounded-3" style="border-color: var(--border-color) !important; background: rgba(255,255,255,0.02);">
                            <i class="bi bi-car-front-fill" style="font-size: 32px; color: var(--color-accent);"></i>
                            <p class="mb-0 mt-2">No live vehicles available yet.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="glass-card" style="height: 100%;">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h4 style="margin-bottom: 4px;">Live Categories</h4>
                    <p class="text-muted mb-0">Categories added in admin update this list automatically.</p>
                </div>
            </div>

            <div class="d-flex flex-column gap-2">
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $category): ?>
                        <div class="d-flex align-items-center justify-content-between p-3 rounded-3" style="border: 1px solid var(--border-color); background: rgba(255,255,255,0.02);">
                            <div>
                                <div class="fw-semibold"><?php echo e((string)($category['name'] ?? 'Category')); ?></div>
                                <div class="text-muted" style="font-size: 12px;"><?php echo e((string)($category['slug'] ?? '')); ?></div>
                            </div>
                            <span class="badge-status badge-confirmed"><?php echo number_format((int)($category['vehicle_count'] ?? 0)); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-muted py-4 border rounded-3" style="border-color: var(--border-color) !important; background: rgba(255,255,255,0.02);">
                        <i class="bi bi-tags-fill" style="font-size: 32px; color: var(--color-accent);"></i>
                        <p class="mb-0 mt-2">No categories yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="glass-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h4 style="margin-bottom: 4px;"><i class="bi bi-bell"></i> Notification Panel</h4>
                    <p class="text-muted mb-0">Live system updates from bookings, test drives, and account changes.</p>
                </div>
                <a href="<?php echo base_url('/notifications'); ?>" class="btn btn-ghost btn-sm">View All</a>
            </div>

            <div style="display: flex; flex-direction: column; gap: 14px;">
                <?php if (!empty($activityFeed)): ?>
                    <?php foreach ($activityFeed as $notification): ?>
                        <div class="p-3 rounded-3" style="border: 1px solid var(--border-color); background: rgba(255,255,255,0.02);">
                            <div class="d-flex align-items-start justify-content-between gap-3">
                                <div>
                                    <div class="fw-semibold"><?php echo e((string)$notification['title']); ?></div>
                                    <div class="text-muted mt-1"><?php echo e((string)$notification['body']); ?></div>
                                </div>
                                <span class="badge-status <?php echo !empty($notification['is_read']) ? 'badge-completed' : 'badge-pending'; ?>">
                                    <?php echo !empty($notification['is_read']) ? 'Read' : 'Unread'; ?>
                                </span>
                            </div>
                            <div class="text-muted mt-2" style="font-size: 12px;">
                                <?php echo e((string)$notification['label']); ?> · <?php echo e((string)$notification['created_at']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-muted py-4 border rounded-3" style="border-color: var(--border-color) !important; background: rgba(255,255,255,0.02);">
                        <i class="bi bi-inbox" style="font-size: 32px; color: var(--color-accent);"></i>
                        <p class="mb-0 mt-2">No notifications yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="glass-card h-100">
            <h4 style="margin-bottom: 24px;"><i class="bi bi-activity"></i> Activity Snapshot</h4>
            <div class="d-grid gap-3">
                <div class="p-3 rounded-3" style="border: 1px solid var(--border-color); background: rgba(255,255,255,0.02);">
                    <div class="text-muted small text-uppercase mb-1">Unread Notifications</div>
                    <div class="fw-semibold fs-4"><?php echo number_format((int)$dashboardStats['notifications']); ?></div>
                </div>
                <div class="p-3 rounded-3" style="border: 1px solid var(--border-color); background: rgba(255,255,255,0.02);">
                    <div class="text-muted small text-uppercase mb-1">Bookings in Progress</div>
                    <div class="fw-semibold fs-4"><?php echo number_format((int)$dashboardStats['active_bookings']); ?></div>
                </div>
                <div class="p-3 rounded-3" style="border: 1px solid var(--border-color); background: rgba(255,255,255,0.02);">
                    <div class="text-muted small text-uppercase mb-1">Test Drive Requests</div>
                    <div class="fw-semibold fs-4"><?php echo number_format((int)$dashboardStats['test_drives']); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="glass-card">
    <h4 style="margin-bottom: 24px;">
        <i class="bi bi-lightning"></i> Quick Actions
    </h4>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px;">
        <a href="<?php echo base_url('/wishlist'); ?>" class="btn btn-primary btn-sm" style="width: 100%;">
            <i class="bi bi-heart"></i> View Wishlist
        </a>
        <a href="<?php echo base_url('/bookings'); ?>" class="btn btn-primary btn-sm" style="width: 100%;">
            <i class="bi bi-calendar-check"></i> My Bookings
        </a>
        <a href="<?php echo base_url('/requests'); ?>" class="btn btn-primary btn-sm" style="width: 100%;">
            <i class="bi bi-file-earmark"></i> Test Drives
        </a>
        <a href="<?php echo base_url('/profile'); ?>" class="btn btn-primary btn-sm" style="width: 100%;">
            <i class="bi bi-person"></i> My Profile
        </a>
    </div>
</div>



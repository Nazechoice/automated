<?php
$vehicle = $vehicle ?? null;
$isAuthenticated = $isAuthenticated ?? false;
$pageTitle = $pageTitle ?? 'Vehicle Details';
$user = $user ?? null;
$relatedVehicles = $relatedVehicles ?? [];
$vehicleTitle = $vehicle ? trim((string)($vehicle['brand'] ?? '') . ' ' . (string)($vehicle['model'] ?? '')) : 'Vehicle Details';
$images = $vehicle['images'] ?? [];
$mainImage = !empty($images[0]['image_url']) ? base_url((string)$images[0]['image_url']) : base_url('assets/img/vehicle-luxury.svg');
$csrfToken = \App\lib\CSRF::token();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/modern.css'); ?>">
</head>
<body>
    <nav class="navbar sticky-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="<?php echo base_url('/'); ?>">
                <i class="bi bi-lightning-charge-fill"></i> AUTOMATES
            </a>
            <div class="d-flex align-items-center gap-3">
                <a href="<?php echo base_url('/'); ?>" class="btn btn-ghost btn-sm">
                    <i class="bi bi-arrow-left"></i> Back Home
                </a>
                <?php if ($isAuthenticated): ?>
                    <a href="<?php echo base_url('/dashboard'); ?>" class="btn btn-accent btn-sm">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                <?php else: ?>
                    <a href="<?php echo base_url('/login'); ?>" class="btn btn-accent btn-sm">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="section">
        <div class="container">
            <?php if (empty($vehicle)): ?>
                <div class="glass-card text-center p-5">
                    <i class="bi bi-exclamation-triangle-fill" style="font-size: 48px; color: var(--color-warning);"></i>
                    <h1 class="h3 mt-3">Vehicle not found</h1>
                    <p class="text-muted mb-4">The car you requested is no longer available or the link is invalid.</p>
                    <a href="<?php echo base_url('/'); ?>" class="btn btn-accent">
                        <i class="bi bi-house"></i> Return Home
                    </a>
                </div>
            <?php else: ?>
                <?php if (!empty($_GET['booked'])): ?>
                    <div class="alert alert-success border-0 glass-card">
                        <i class="bi bi-check-circle-fill me-2"></i>Your booking request has been submitted.
                    </div>
                <?php endif; ?>

                <?php if (!empty($_GET['test_drive'])): ?>
                    <div class="alert alert-success border-0 glass-card">
                        <i class="bi bi-check-circle-fill me-2"></i>Your test drive request has been submitted.
                    </div>
                <?php endif; ?>

                <?php if (!empty($_GET['error'])): ?>
                    <div class="alert alert-danger border-0 glass-card">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>We could not process that request. Please try again.
                    </div>
                <?php endif; ?>

                <div class="row g-4 align-items-start">
                    <div class="col-lg-7">
                        <div class="glass-card p-3">
                            <img src="<?php echo e($mainImage); ?>" alt="<?php echo e($vehicleTitle); ?>" class="img-fluid rounded-4 w-100" style="object-fit: cover; max-height: 500px;">
                            <?php if (!empty($images) && count($images) > 1): ?>
                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    <?php foreach ($images as $image): ?>
                                        <img src="<?php echo e(base_url((string)$image['image_url'])); ?>" alt="<?php echo e($vehicleTitle); ?>" class="rounded-3" style="width: 96px; height: 72px; object-fit: cover; border: 1px solid var(--border-color);">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="glass-card h-100">
                            <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                                <span class="badge-status badge-confirmed"><?php echo e(!empty($vehicle['featured']) ? 'Featured' : 'Live'); ?></span>
                                <span class="badge-status badge-pending"><?php echo e(ucfirst((string)($vehicle['status'] ?? 'active'))); ?></span>
                            </div>

                            <h1 class="h2 mb-2"><?php echo e($vehicleTitle); ?></h1>
                            <p class="text-muted mb-4"><?php echo e((string)($vehicle['category_name'] ?? 'Uncategorized')); ?>, <?php echo e((string)($vehicle['year'] ?? '')); ?></p>

                            <div class="vehicle-card-price mb-4">
                                <?php echo e((string)($vehicle['currency'] ?? 'USD')); ?> <?php echo number_format((float)($vehicle['price'] ?? 0)); ?>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-6">
                                    <div class="p-3 rounded-3 border" style="border-color: var(--border-color) !important;">
                                        <div class="text-muted small mb-1">Mileage</div>
                                        <div class="fw-semibold"><?php echo !empty($vehicle['mileage_km']) ? e(number_format((float)$vehicle['mileage_km']) . ' km') : 'Brand new'; ?></div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 rounded-3 border" style="border-color: var(--border-color) !important;">
                                        <div class="text-muted small mb-1">Transmission</div>
                                        <div class="fw-semibold"><?php echo e(ucfirst((string)($vehicle['transmission'] ?? 'automatic'))); ?></div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 rounded-3 border" style="border-color: var(--border-color) !important;">
                                        <div class="text-muted small mb-1">Fuel</div>
                                        <div class="fw-semibold"><?php echo e(ucfirst((string)($vehicle['fuel_type'] ?? 'petrol'))); ?></div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 rounded-3 border" style="border-color: var(--border-color) !important;">
                                        <div class="text-muted small mb-1">Seats</div>
                                        <div class="fw-semibold"><?php echo !empty($vehicle['seating_capacity']) ? e((string)$vehicle['seating_capacity']) : 'N/A'; ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h2 class="h5 mb-2">Description</h2>
                                <p class="text-muted mb-0"><?php echo e((string)($vehicle['description'] ?: 'This vehicle is ready for customer viewing and can be updated by the admin at any time.')); ?></p>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                <button class="btn btn-accent wishlist-btn" type="button" data-vehicle-id="<?php echo (int)($vehicle['id'] ?? 0); ?>">
                                    <i class="bi bi-heart"></i> Wishlist
                                </button>
                                <button class="btn btn-ghost compare-btn" type="button" data-vehicle-id="<?php echo (int)($vehicle['id'] ?? 0); ?>">
                                    <i class="bi bi-shuffle"></i> Compare
                                </button>
                                <a href="<?php echo base_url('/vehicles'); ?>" class="btn btn-outline-light">
                                    <i class="bi bi-search"></i> Browse Catalog
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="glass-card mb-4">
                            <h2 class="h5 mb-3"><i class="bi bi-calendar-check"></i> Book This Vehicle</h2>
                            <?php if ($isAuthenticated): ?>
                                <form method="post" action="<?php echo base_url('/vehicle/book'); ?>" class="row g-3">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="vehicle_id" value="<?php echo (int)($vehicle['id'] ?? 0); ?>">
                                    <div class="col-12">
                                        <label class="form-label">Name</label>
                                        <input type="text" name="contact_name" class="form-control glass-input" value="<?php echo e((string)($user->full_name ?? '')); ?>">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="contact_email" class="form-control glass-input" value="<?php echo e((string)($user->email ?? '')); ?>">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Phone</label>
                                        <input type="text" name="contact_phone" class="form-control glass-input" value="<?php echo e((string)($user->phone ?? '')); ?>">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Notes</label>
                                        <textarea name="notes" rows="3" class="form-control glass-input" placeholder="Tell us about your preferred purchase timeline or questions"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-accent w-100">
                                            <i class="bi bi-send"></i> Submit Booking Request
                                        </button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <p class="text-muted mb-3">Login to request a booking for this vehicle.</p>
                                <a href="<?php echo base_url('/login'); ?>" class="btn btn-accent w-100">Login to Book</a>
                            <?php endif; ?>
                        </div>

                        <div class="glass-card">
                            <h2 class="h5 mb-3"><i class="bi bi-clock-history"></i> Test Drive Request</h2>
                            <?php if ($isAuthenticated): ?>
                                <form method="post" action="<?php echo base_url('/vehicle/test-drive'); ?>" class="row g-3">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="vehicle_id" value="<?php echo (int)($vehicle['id'] ?? 0); ?>">
                                    <div class="col-12">
                                        <label class="form-label">Name</label>
                                        <input type="text" name="name" class="form-control glass-input" value="<?php echo e((string)($user->full_name ?? '')); ?>">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control glass-input" value="<?php echo e((string)($user->email ?? '')); ?>">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Phone</label>
                                        <input type="text" name="phone" class="form-control glass-input" value="<?php echo e((string)($user->phone ?? '')); ?>">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Preferred Date & Time</label>
                                        <input type="datetime-local" name="preferred_datetime" class="form-control glass-input">
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-ghost w-100">
                                            <i class="bi bi-send-check"></i> Request Test Drive
                                        </button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <p class="text-muted mb-3">Login to request a test drive for this vehicle.</p>
                                <a href="<?php echo base_url('/login'); ?>" class="btn btn-ghost w-100">Login to Request</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if (!empty($relatedVehicles)): ?>
                    <div class="mt-5">
                        <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
                            <div>
                                <h2 class="h3 mb-1">Related Vehicles</h2>
                                <p class="text-muted mb-0">More cars customers often compare with this one.</p>
                            </div>
                            <a href="<?php echo base_url('/vehicles'); ?>" class="btn btn-ghost">View Full Catalog</a>
                        </div>

                        <div class="vehicle-grid">
                            <?php foreach ($relatedVehicles as $related): ?>
                                <?php
                                $relatedTitle = trim((string)$related['brand'] . ' ' . (string)$related['model']);
                                $relatedImage = base_url((string)$related['cover']);
                                ?>
                                <div class="vehicle-card">
                                    <div class="vehicle-card-image">
                                        <img src="<?php echo e($relatedImage); ?>" alt="<?php echo e($relatedTitle); ?>">
                                        <span class="vehicle-card-badge"><?php echo e(!empty($related['featured']) ? 'Featured' : 'Live'); ?></span>
                                    </div>
                                    <div class="vehicle-card-content">
                                        <h4><?php echo e($relatedTitle . ' ' . (string)$related['year']); ?></h4>
                                        <div class="vehicle-card-meta">
                                            <span><i class="bi bi-tag"></i> <?php echo e((string)($related['category_name'] ?? 'Uncategorized')); ?></span>
                                            <span><i class="bi bi-speedometer"></i> <?php echo !empty($related['mileage_km']) ? e(number_format((float)$related['mileage_km']) . ' km') : 'Brand new'; ?></span>
                                        </div>
                                        <div class="vehicle-card-price"><?php echo e((string)($related['currency'] ?? 'USD')); ?> <?php echo number_format((float)$related['price']); ?></div>
                                        <a href="<?php echo base_url('/vehicle?id=' . (int)$related['id']); ?>" class="btn btn-primary btn-sm vehicle-card-btn">View Details</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const isAuthenticated = <?php echo json_encode($isAuthenticated); ?>;
        const loginUrl = <?php echo json_encode(base_url('/login')); ?>;
        const csrfToken = <?php echo json_encode($csrfToken); ?>;

        document.querySelectorAll('.wishlist-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                if (!isAuthenticated) {
                    window.location.href = loginUrl;
                    return;
                }
                const vehicleId = btn.dataset.vehicleId;
                try {
                    const response = await fetch('<?php echo base_url('/api/wishlist/toggle'); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ vehicle_id: vehicleId, csrf_token: csrfToken })
                    });
                    const data = await response.json();
                    btn.classList.toggle('active');
                    btn.style.background = data.added ? 'var(--color-danger)' : 'rgba(255, 255, 255, 0.1)';
                } catch (error) {
                    console.error('Error:', error);
                }
            });
        });

        document.querySelectorAll('.compare-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                if (!isAuthenticated) {
                    window.location.href = loginUrl;
                    return;
                }
                const vehicleId = btn.dataset.vehicleId;
                try {
                    await fetch('<?php echo base_url('/api/compare/toggle'); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ vehicle_id: vehicleId, csrf_token: csrfToken })
                    });
                    btn.classList.toggle('active');
                } catch (error) {
                    console.error('Error:', error);
                }
            });
        });
    </script>
</body>
</html>

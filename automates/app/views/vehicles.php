<?php
$pageTitle = $pageTitle ?? 'Browse Vehicles | AUTOMATES';
$user = $user ?? null;
$isAuthenticated = $isAuthenticated ?? false;
$categories = $categories ?? [];
$vehicles = $vehicles ?? [];
$filters = $filters ?? [
    'q' => '',
    'category' => '',
    'fuel' => '',
    'transmission' => '',
    'min_price' => '',
    'max_price' => '',
];
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
    <style>
        .catalog-hero {
            background: linear-gradient(135deg, rgba(0, 212, 255, 0.08), rgba(26, 58, 82, 0.18));
            border-bottom: 1px solid var(--border-color);
            padding: 48px 0 24px;
        }
        .catalog-filters {
            background: var(--bg-glass);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 20px;
            backdrop-filter: blur(10px);
            margin-top: 24px;
        }
        .catalog-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            border: 1px solid var(--border-color);
            background: rgba(255,255,255,0.03);
            color: var(--text-secondary);
            text-decoration: none;
            transition: all .2s ease;
        }
        .catalog-chip:hover,
        .catalog-chip.active {
            border-color: var(--color-accent);
            color: var(--color-accent);
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <nav class="navbar sticky-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="<?php echo base_url('/'); ?>">
                <i class="bi bi-lightning-charge-fill"></i> AUTOMATES
            </a>
            <div class="d-flex align-items-center gap-3">
                <a href="<?php echo base_url('/'); ?>" class="btn btn-ghost btn-sm">
                    <i class="bi bi-house"></i> Home
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

    <header class="catalog-hero">
        <div class="container">
            <div class="row align-items-end g-4">
                <div class="col-lg-7">
                    <h1 class="display-5 mb-3">Browse Vehicles</h1>
                    <p class="lead text-muted mb-0">Search every live vehicle in the showroom, filter by category, and jump into a detail page with related cars and booking actions.</p>
                </div>
                <div class="col-lg-5">
                    <div class="catalog-filters">
                        <form method="get" action="<?php echo base_url('/vehicles'); ?>" class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Search</label>
                                <input type="text" name="q" class="form-control glass-input" placeholder="BMW, SUV, electric..." value="<?php echo e((string)$filters['q']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select glass-input">
                                    <option value="">All</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo e((string)$category['slug']); ?>" <?php echo ((string)$filters['category'] === (string)$category['slug'] ? 'selected' : ''); ?>>
                                            <?php echo e((string)$category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fuel</label>
                                <select name="fuel" class="form-select glass-input">
                                    <option value="">All</option>
                                    <?php foreach (['petrol' => 'Petrol', 'diesel' => 'Diesel', 'hybrid' => 'Hybrid', 'electric' => 'Electric'] as $value => $label): ?>
                                        <option value="<?php echo e($value); ?>" <?php echo ((string)$filters['fuel'] === $value ? 'selected' : ''); ?>>
                                            <?php echo e($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Transmission</label>
                                <select name="transmission" class="form-select glass-input">
                                    <option value="">All</option>
                                    <option value="automatic" <?php echo ((string)$filters['transmission'] === 'automatic' ? 'selected' : ''); ?>>Automatic</option>
                                    <option value="manual" <?php echo ((string)$filters['transmission'] === 'manual' ? 'selected' : ''); ?>>Manual</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Min Price</label>
                                <input type="number" name="min_price" class="form-control glass-input" value="<?php echo e((string)$filters['min_price']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Max Price</label>
                                <input type="number" name="max_price" class="form-control glass-input" value="<?php echo e((string)$filters['max_price']); ?>">
                            </div>
                            <div class="col-12 d-flex gap-2 flex-wrap">
                                <button type="submit" class="btn btn-accent">
                                    <i class="bi bi-funnel"></i> Apply Filters
                                </button>
                                <a href="<?php echo base_url('/vehicles'); ?>" class="btn btn-ghost">
                                    Clear
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="section">
        <div class="container">
            <div class="d-flex flex-wrap gap-2 mb-4">
                <a class="catalog-chip <?php echo ($filters['category'] === '' ? 'active' : ''); ?>" href="<?php echo base_url('/vehicles'); ?>">
                    <i class="bi bi-grid"></i> All
                </a>
                <?php foreach ($categories as $category): ?>
                    <a class="catalog-chip <?php echo ((string)$filters['category'] === (string)$category['slug'] ? 'active' : ''); ?>" href="<?php echo base_url('/vehicles?category=' . rawurlencode((string)$category['slug'])); ?>">
                        <i class="bi bi-tag"></i> <?php echo e((string)$category['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <h2 class="h4 mb-1">Live Inventory</h2>
                    <p class="text-muted mb-0"><?php echo number_format(count($vehicles)); ?> vehicle(s) found</p>
                </div>
                <a href="<?php echo base_url('/'); ?>" class="btn btn-ghost">
                    <i class="bi bi-arrow-left"></i> Back to Home
                </a>
            </div>

            <div class="vehicle-grid">
                <?php if (!empty($vehicles)): ?>
                    <?php foreach ($vehicles as $vehicle): ?>
                        <?php
                        $vehicleTitle = trim((string)$vehicle['brand'] . ' ' . (string)$vehicle['model']);
                        $image = base_url((string)$vehicle['cover']);
                        ?>
                        <div class="vehicle-card">
                            <div class="vehicle-card-image">
                                <img src="<?php echo e($image); ?>" alt="<?php echo e($vehicleTitle); ?>">
                                <span class="vehicle-card-badge"><?php echo e(!empty($vehicle['featured']) ? 'Featured' : ucfirst((string)$vehicle['status'])); ?></span>
                            </div>
                            <div class="vehicle-card-content">
                                <h4><?php echo e($vehicleTitle . ' ' . (string)$vehicle['year']); ?></h4>
                                <div class="vehicle-card-meta">
                                    <span><i class="bi bi-tag"></i> <?php echo e((string)($vehicle['category_name'] ?? 'Uncategorized')); ?></span>
                                    <span><i class="bi bi-speedometer"></i> <?php echo !empty($vehicle['mileage_km']) ? e(number_format((float)$vehicle['mileage_km']) . ' km') : 'Brand new'; ?></span>
                                </div>
                                <div class="vehicle-card-meta">
                                    <span><i class="bi bi-fuel-pump"></i> <?php echo e(ucfirst((string)$vehicle['fuel_type'])); ?></span>
                                    <span><i class="bi bi-gear"></i> <?php echo e(ucfirst((string)$vehicle['transmission'])); ?></span>
                                </div>
                                <div class="vehicle-card-price"><?php echo e((string)($vehicle['currency'] ?? 'USD')); ?> <?php echo number_format((float)$vehicle['price']); ?></div>
                                <a href="<?php echo base_url('/vehicle?id=' . (int)$vehicle['id']); ?>" class="btn btn-primary btn-sm vehicle-card-btn">View Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="glass-card text-center" style="grid-column: 1 / -1; padding: 44px;">
                        <i class="bi bi-search" style="font-size: 42px; color: var(--color-accent);"></i>
                        <h3 class="h4 mt-3 mb-2">No vehicles match these filters</h3>
                        <p class="text-muted mb-4">Try broadening your search or clear the filters to view the full catalog.</p>
                        <a href="<?php echo base_url('/vehicles'); ?>" class="btn btn-accent">Reset Filters</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

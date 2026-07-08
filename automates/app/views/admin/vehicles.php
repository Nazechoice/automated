<?php
$categories = $categories ?? [];
$vehicles = $vehicles ?? [];
$counts = $counts ?? ['total_vehicles' => 0, 'active_vehicles' => 0, 'featured_vehicles' => 0];
$errors = $errors ?? [];
$old = $old ?? [];
$successMessage = $successMessage ?? null;
$editVehicle = $editVehicle ?? null;
$editVehicleId = (int)($editVehicle['id'] ?? 0);
$isEditing = $editVehicleId > 0;
$formTitle = $isEditing ? 'Edit Vehicle Card' : 'Create Vehicle Card';
$formDescription = $isEditing ? 'Update the selected vehicle and refresh its images.' : 'Add a new car to the showroom and homepage catalog.';
$submitLabel = $isEditing ? 'Update Vehicle Card' : 'Create Vehicle Card';
?>

<div class="admin-header">
    <div>
        <h1><i class="bi bi-car-front-fill"></i> Vehicle Management</h1>
        <p class="text-muted mt-2">Create premium car cards, manage inventory, and feature vehicles on the home page.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?php echo base_url('/admin'); ?>" class="btn btn-ghost">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="<?php echo base_url('/'); ?>" class="btn btn-accent">
            <i class="bi bi-globe2"></i> View Site
        </a>
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
        <div class="admin-stat-label">Total Vehicles</div>
        <div class="admin-stat-value"><?php echo number_format((int)$counts['total_vehicles']); ?></div>
        <div class="admin-stat-trend">Inventory records</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Active Vehicles</div>
        <div class="admin-stat-value"><?php echo number_format((int)$counts['active_vehicles']); ?></div>
        <div class="admin-stat-trend">Visible to customers</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Featured Cards</div>
        <div class="admin-stat-value"><?php echo number_format((int)$counts['featured_vehicles']); ?></div>
        <div class="admin-stat-trend">Homepage spotlight</div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-5">
        <div class="admin-chart">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h3 class="h5 mb-1"><?php echo e($formTitle); ?></h3>
                    <p class="text-muted mb-0"><?php echo e($formDescription); ?></p>
                </div>
                <span class="badge-status badge-confirmed">Admin only</span>
            </div>

            <form method="post" action="<?php echo base_url('/admin/vehicles'); ?>" class="row g-3" enctype="multipart/form-data">
                <?php csrf_field(); ?>
                <input type="hidden" name="vehicle_id" value="<?php echo e((string)($old['vehicle_id'] ?? '')); ?>">

                <div class="col-md-6">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select glass-input">
                        <option value="">No Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo (int)$category['id']; ?>" <?php echo ((string)($old['category_id'] ?? '') === (string)$category['id'] ? 'selected' : ''); ?>>
                                <?php echo e((string)$category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select glass-input">
                        <?php foreach (['active' => 'Active', 'inactive' => 'Inactive', 'sold' => 'Sold'] as $value => $label): ?>
                            <option value="<?php echo e($value); ?>" <?php echo (($old['status'] ?? 'active') === $value ? 'selected' : ''); ?>>
                                <?php echo e($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Brand</label>
                    <input type="text" name="brand" class="form-control glass-input" placeholder="BMW" value="<?php echo e((string)($old['brand'] ?? '')); ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Model</label>
                    <input type="text" name="model" class="form-control glass-input" placeholder="X7 M50i" value="<?php echo e((string)($old['model'] ?? '')); ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Year</label>
                    <input type="number" name="year" class="form-control glass-input" min="1990" max="2100" value="<?php echo e((string)($old['year'] ?? '')); ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Price</label>
                    <input type="number" step="0.01" name="price" class="form-control glass-input" min="0" placeholder="85000" value="<?php echo e((string)($old['price'] ?? '')); ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Currency</label>
                    <input type="text" name="currency" class="form-control glass-input" maxlength="3" value="<?php echo e((string)($old['currency'] ?? 'USD')); ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Mileage (km)</label>
                    <input type="number" name="mileage_km" class="form-control glass-input" min="0" value="<?php echo e((string)($old['mileage_km'] ?? '')); ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Fuel Type</label>
                    <select name="fuel_type" class="form-select glass-input">
                        <?php foreach (['petrol' => 'Petrol', 'diesel' => 'Diesel', 'hybrid' => 'Hybrid', 'electric' => 'Electric'] as $value => $label): ?>
                            <option value="<?php echo e($value); ?>" <?php echo (($old['fuel_type'] ?? 'petrol') === $value ? 'selected' : ''); ?>>
                                <?php echo e($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Transmission</label>
                    <select name="transmission" class="form-select glass-input">
                        <?php foreach (['automatic' => 'Automatic', 'manual' => 'Manual'] as $value => $label): ?>
                            <option value="<?php echo e($value); ?>" <?php echo (($old['transmission'] ?? 'automatic') === $value ? 'selected' : ''); ?>>
                                <?php echo e($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Seating Capacity</label>
                    <input type="number" name="seating_capacity" class="form-control glass-input" min="1" max="20" value="<?php echo e((string)($old['seating_capacity'] ?? '')); ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Horsepower</label>
                    <input type="number" name="horsepower" class="form-control glass-input" min="1" value="<?php echo e((string)($old['horsepower'] ?? '')); ?>">
                </div>

                <div class="col-12">
                    <label class="form-label">Upload Vehicle Images</label>
                    <input type="file" name="vehicle_images[]" class="form-control glass-input" accept="image/png,image/jpeg,image/webp,image/gif" multiple>
                    <div class="text-muted mt-2">Upload one or more vehicle photos. If no image is uploaded, the showroom placeholder will be used.</div>
                </div>

                <?php if ($isEditing && !empty($editVehicle['images'])): ?>
                    <div class="col-12">
                        <label class="form-label">Current Images</label>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($editVehicle['images'] as $image): ?>
                                <img src="<?php echo e(base_url((string)$image['image_url'])); ?>" alt="Vehicle image" style="width: 92px; height: 72px; object-fit: cover; border-radius: 12px; border: 1px solid var(--border-color);">
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="4" class="form-control glass-input" placeholder="Premium features, service history, and special notes"><?php echo e((string)($old['description'] ?? '')); ?></textarea>
                </div>

                <div class="col-12 d-flex align-items-center justify-content-between gap-3 flex-wrap">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="featured" id="featured" value="1" <?php echo (($old['featured'] ?? '1') === '1' ? 'checked' : ''); ?>>
                        <label class="form-check-label" for="featured">Feature this vehicle on the homepage</label>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <?php if ($isEditing): ?>
                            <a href="<?php echo base_url('/admin/vehicles'); ?>" class="btn btn-ghost">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-accent">
                            <i class="bi bi-save"></i> <?php echo e($submitLabel); ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="admin-chart">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h3 class="h5 mb-1">Vehicle Cards</h3>
                    <p class="text-muted mb-0">These are the cards shown in inventory and on the homepage.</p>
                </div>
                <span class="badge-status badge-pending"><?php echo number_format(count($vehicles)); ?> records</span>
            </div>

            <div class="row g-3">
                <?php if (!empty($vehicles)): ?>
                    <?php foreach ($vehicles as $vehicle): ?>
                        <div class="col-md-6">
                            <div class="vehicle-card h-100">
                                <div class="vehicle-card-image">
                                    <img src="<?php echo e(base_url((string)$vehicle['cover'])); ?>" alt="<?php echo e((string)$vehicle['brand'] . ' ' . (string)$vehicle['model']); ?>">
                                    <span class="vehicle-card-badge"><?php echo e((string)($vehicle['featured'] ? 'Featured' : ucfirst((string)$vehicle['status']))); ?></span>
                                </div>
                                <div class="vehicle-card-content">
                                    <h4><?php echo e((string)$vehicle['brand'] . ' ' . (string)$vehicle['model']); ?></h4>
                                    <div class="vehicle-card-meta">
                                        <span><i class="bi bi-calendar3"></i> <?php echo e((string)$vehicle['year']); ?></span>
                                        <span><i class="bi bi-gear"></i> <?php echo e(ucfirst((string)$vehicle['transmission'])); ?></span>
                                    </div>
                                    <div class="vehicle-card-meta">
                                        <span><i class="bi bi-fuel-pump"></i> <?php echo e(ucfirst((string)$vehicle['fuel_type'])); ?></span>
                                        <span><i class="bi bi-tags"></i> <?php echo e((string)($vehicle['category_name'] ?? 'Uncategorized')); ?></span>
                                    </div>
                                    <div class="vehicle-card-price">
                                        <?php echo e((string)($vehicle['currency'] ?? 'USD')); ?> <?php echo number_format((float)$vehicle['price']); ?>
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <span class="badge-status badge-confirmed"><?php echo e((string)$vehicle['status']); ?></span>
                                        <?php if (!empty($vehicle['featured'])): ?>
                                            <span class="badge-status badge-completed">Homepage</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap mt-3">
                                        <a href="<?php echo base_url('/admin/vehicles?edit=' . (int)$vehicle['id']); ?>" class="btn btn-outline-light btn-sm">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <form method="post" action="<?php echo base_url('/admin/vehicles'); ?>" onsubmit="return confirm('Delete this vehicle card?');" class="mb-0">
                                            <?php csrf_field(); ?>
                                            <input type="hidden" name="delete_id" value="<?php echo (int)$vehicle['id']; ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="glass-card p-4 text-center">
                            <h4 class="h5 mb-2">No vehicles yet</h4>
                            <p class="text-muted mb-0">Use the form on the left to create the first car card.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

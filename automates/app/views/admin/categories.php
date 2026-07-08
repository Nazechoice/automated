<?php
$categories = $categories ?? [];
$counts = $counts ?? ['total_categories' => 0, 'total_assigned' => 0];
$errors = $errors ?? [];
$old = $old ?? ['category_id' => '', 'name' => '', 'slug' => ''];
$successMessage = $successMessage ?? null;
$editCategory = $editCategory ?? null;
$editCategoryId = (int)($editCategory['id'] ?? 0);
$isEditing = $editCategoryId > 0;
$formTitle = $isEditing ? 'Edit Category' : 'Create Category';
$submitLabel = $isEditing ? 'Update Category' : 'Create Category';
?>

<div class="admin-header">
    <div>
        <h1><i class="bi bi-tag-fill"></i> Categories Management</h1>
        <p class="text-muted mt-2">Create and organize vehicle categories used by showroom cards and filters.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?php echo base_url('/admin/vehicles'); ?>" class="btn btn-ghost">
            <i class="bi bi-car-front-fill"></i> Vehicles
        </a>
        <a href="<?php echo base_url('/admin'); ?>" class="btn btn-accent">
            <i class="bi bi-speedometer2"></i> Dashboard
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
        <div class="admin-stat-label">Categories</div>
        <div class="admin-stat-value"><?php echo number_format((int)$counts['total_categories']); ?></div>
        <div class="admin-stat-trend">Vehicle group records</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Assigned Vehicles</div>
        <div class="admin-stat-value"><?php echo number_format((int)$counts['total_assigned']); ?></div>
        <div class="admin-stat-trend">Vehicles grouped by category</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Category State</div>
        <div class="admin-stat-value">Live</div>
        <div class="admin-stat-trend">Connected to the vehicle cards</div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-5">
        <div class="admin-chart">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h3 class="h5 mb-1"><?php echo e($formTitle); ?></h3>
                    <p class="text-muted mb-0">Use a clear category name so vehicle cards stay organized.</p>
                </div>
                <span class="badge-status badge-confirmed">Admin only</span>
            </div>

            <form method="post" action="<?php echo base_url('/admin/categories'); ?>" class="row g-3">
                <?php csrf_field(); ?>
                <input type="hidden" name="category_id" value="<?php echo e((string)($old['category_id'] ?? '')); ?>">

                <div class="col-12">
                    <label class="form-label">Category Name</label>
                    <input type="text" name="name" class="form-control glass-input" placeholder="Luxury" value="<?php echo e((string)($old['name'] ?? '')); ?>">
                </div>

                <div class="col-12">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" class="form-control glass-input" placeholder="luxury" value="<?php echo e((string)($old['slug'] ?? '')); ?>">
                    <div class="text-muted mt-2">Leave blank to auto-generate from the category name.</div>
                </div>

                <div class="col-12 d-flex align-items-center justify-content-between gap-3 flex-wrap">
                    <div class="text-muted small">
                        Vehicles assigned: <?php echo number_format((int)$counts['total_assigned']); ?>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <?php if ($isEditing): ?>
                            <a href="<?php echo base_url('/admin/categories'); ?>" class="btn btn-ghost">
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
                    <h3 class="h5 mb-1">Category List</h3>
                    <p class="text-muted mb-0">Categories are tied directly to vehicle cards and filters.</p>
                </div>
                <span class="badge-status badge-pending"><?php echo number_format(count($categories)); ?> records</span>
            </div>

            <div class="admin-table mb-0">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Vehicles</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td class="fw-semibold"><?php echo e((string)$category['name']); ?></td>
                                    <td><code><?php echo e((string)$category['slug']); ?></code></td>
                                    <td><?php echo number_format((int)($category['vehicle_count'] ?? 0)); ?></td>
                                    <td>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <a href="<?php echo base_url('/admin/categories?edit=' . (int)$category['id']); ?>" class="btn btn-outline-light btn-sm">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </a>
                                            <form method="post" action="<?php echo base_url('/admin/categories'); ?>" onsubmit="return confirm('Delete this category? Vehicles will become uncategorized.');" class="mb-0">
                                                <?php csrf_field(); ?>
                                                <input type="hidden" name="delete_id" value="<?php echo (int)$category['id']; ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No categories yet. Create the first one on the left.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

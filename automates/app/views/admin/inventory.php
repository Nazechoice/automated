<?php
$inventory = $inventory ?? [];
$successMessage = $successMessage ?? null;
$errors = $errors ?? [];
?>

<div class="admin-header">
    <div>
        <h1><i class="bi bi-boxes"></i> Inventory Management</h1>
        <p class="text-muted mt-2">Keep stock counts in sync with the showroom and vehicle cards.</p>
    </div>
    <a href="<?php echo base_url('/admin/vehicles'); ?>" class="btn btn-accent">
        <i class="bi bi-car-front-fill"></i> Vehicles
    </a>
</div>

<?php if ($successMessage): ?>
    <div class="alert alert-success border-0 glass-card">
        <i class="bi bi-check-circle-fill me-2"></i><?php echo e($successMessage); ?>
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger border-0 glass-card">
        <ul class="mb-0 ps-3">
            <?php foreach ($errors as $error): ?>
                <li><?php echo e((string)$error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="admin-table">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>Vehicle</th>
                <th>Category</th>
                <th>Stock</th>
                <th>Price</th>
                <th>Update</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($inventory)): ?>
                <?php foreach ($inventory as $item): ?>
                    <tr>
                        <td class="fw-semibold"><?php echo e(trim((string)$item['brand'] . ' ' . (string)$item['model'])); ?></td>
                        <td><?php echo e((string)($item['category_name'] ?? 'Uncategorized')); ?></td>
                        <td><?php echo number_format((int)$item['stock_qty']); ?></td>
                        <td><?php echo e((string)($item['currency'] ?? 'USD')); ?> <?php echo number_format((float)$item['price']); ?></td>
                        <td style="min-width: 220px;">
                            <form method="post" action="<?php echo base_url('/admin/inventory'); ?>" class="d-flex gap-2 align-items-center">
                                <?php csrf_field(); ?>
                                <input type="hidden" name="inventory_id" value="<?php echo (int)$item['id']; ?>">
                                <input type="number" name="stock_qty" min="0" class="form-control form-control-sm glass-input" value="<?php echo (int)$item['stock_qty']; ?>" style="max-width: 120px;">
                                <button type="submit" class="btn btn-sm btn-accent">Save</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">No inventory items found yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

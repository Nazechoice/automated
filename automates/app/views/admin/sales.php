<?php
$sales = $sales ?? [];
$metrics = $metrics ?? ['total_sales' => 0, 'revenue' => 0, 'average_sale' => 0];
?>

<div class="admin-header">
    <div>
        <h1><i class="bi bi-graph-up-arrow"></i> Sales Management</h1>
        <p class="text-muted mt-2">Track completed deals, revenue, and average transaction value.</p>
    </div>
    <a href="<?php echo base_url('/admin/reports'); ?>" class="btn btn-accent">
        <i class="bi bi-file-earmark-bar-graph"></i> Reports
    </a>
</div>

<div class="admin-stats">
    <div class="admin-stat-card">
        <div class="admin-stat-label">Total Sales</div>
        <div class="admin-stat-value"><?php echo number_format((int)$metrics['total_sales']); ?></div>
        <div class="admin-stat-trend">Closed deals</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Revenue</div>
        <div class="admin-stat-value"><?php echo number_format((float)$metrics['revenue'], 0); ?></div>
        <div class="admin-stat-trend">Gross sales amount</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Average Sale</div>
        <div class="admin-stat-value"><?php echo number_format((float)$metrics['average_sale'], 0); ?></div>
        <div class="admin-stat-trend">Average deal size</div>
    </div>
</div>

<div class="admin-table">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Vehicle</th>
                <th>Amount</th>
                <th>Sold At</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($sales)): ?>
                <?php foreach ($sales as $sale): ?>
                    <tr>
                        <td class="fw-semibold"><?php echo e((string)$sale['customer_name']); ?></td>
                        <td><?php echo e(trim((string)($sale['brand'] ?? '') . ' ' . (string)($sale['model'] ?? '')) ?: 'N/A'); ?></td>
                        <td><?php echo e((string)($sale['currency'] ?? 'USD')); ?> <?php echo number_format((float)$sale['sale_amount']); ?></td>
                        <td><?php echo e((string)$sale['sold_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">No sales records found yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

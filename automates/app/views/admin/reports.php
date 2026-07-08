<?php
$monthly = $monthly ?? [];
$summary = $summary ?? ['daily_sales' => 0, 'monthly_sales' => 0, 'yearly_sales' => 0];
$vehicles = $vehicles ?? [];
$categories = $categories ?? [];
$focusSection = $focusSection ?? 'all';
$chartRows = array_reverse($monthly);
$chartLabels = array_map(static fn(array $row): string => (string)$row['month'], $chartRows);
$chartCounts = array_map(static fn(array $row): int => (int)$row['sales_count'], $chartRows);
$chartRevenue = array_map(static fn(array $row): float => (float)$row['revenue'], $chartRows);
?>

<div class="admin-header">
    <div>
        <h1><i class="bi bi-file-earmark-bar-graph-fill"></i> Reports</h1>
        <p class="text-muted mt-2">Sales, vehicle, and category reporting with PDF, Excel, and print export options.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?php echo base_url('/admin/reports/print?report=all'); ?>" class="btn btn-ghost btn-sm">
            <i class="bi bi-printer"></i> Print Report
        </a>
        <a href="<?php echo base_url('/admin/reports/export/pdf?report=all'); ?>" class="btn btn-ghost btn-sm">
            <i class="bi bi-filetype-pdf"></i> Export PDF
        </a>
        <a href="<?php echo base_url('/admin/reports/export/excel?report=all'); ?>" class="btn btn-accent btn-sm">
            <i class="bi bi-file-excel"></i> Export Excel
        </a>
    </div>
</div>

<div class="admin-stats">
    <div class="admin-stat-card">
        <div class="admin-stat-label">Daily Sales</div>
        <div class="admin-stat-value"><?php echo number_format((float)$summary['daily_sales'], 0); ?></div>
        <div class="admin-stat-trend">Today</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Monthly Sales</div>
        <div class="admin-stat-value"><?php echo number_format((float)$summary['monthly_sales'], 0); ?></div>
        <div class="admin-stat-trend">Current month</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Yearly Sales</div>
        <div class="admin-stat-value"><?php echo number_format((float)$summary['yearly_sales'], 0); ?></div>
        <div class="admin-stat-trend">Current year</div>
    </div>
</div>

<div class="admin-chart">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
        <div>
            <h3 class="h5 mb-1">Monthly Revenue Trend</h3>
            <p class="text-muted mb-0">Sales totals are pulled from the live sales table.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="#sales-report" class="badge-status badge-confirmed text-decoration-none">Sales Report</a>
            <a href="#vehicle-report" class="badge-status badge-confirmed text-decoration-none">Vehicle Report</a>
            <a href="#category-report" class="badge-status badge-confirmed text-decoration-none">Category Report</a>
        </div>
    </div>
    <canvas id="reportsChart" height="90"></canvas>
</div>

<div id="sales-report" class="admin-table">
    <div style="padding: 20px; border-bottom: 1px solid var(--border-color);">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h5 style="margin-bottom: 0;">Sales Report</h5>
                <p class="text-muted mb-0">Monthly sales totals and revenue.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="<?php echo base_url('/admin/reports/print?report=sales'); ?>" class="btn btn-ghost btn-sm">Print</a>
                <a href="<?php echo base_url('/admin/reports/export/pdf?report=sales'); ?>" class="btn btn-ghost btn-sm">PDF</a>
                <a href="<?php echo base_url('/admin/reports/export/excel?report=sales'); ?>" class="btn btn-accent btn-sm">Excel</a>
            </div>
        </div>
    </div>
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>Month</th>
                <th>Sales Count</th>
                <th>Revenue</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($chartRows)): ?>
                <?php foreach ($chartRows as $row): ?>
                    <tr>
                        <td class="fw-semibold"><?php echo e((string)$row['month']); ?></td>
                        <td><?php echo number_format((int)$row['sales_count']); ?></td>
                        <td><?php echo number_format((float)$row['revenue'], 0); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" class="text-center text-muted py-4">No sales history yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="vehicle-report" class="admin-table">
    <div style="padding: 20px; border-bottom: 1px solid var(--border-color);">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h5 style="margin-bottom: 0;">Vehicle Report</h5>
                <p class="text-muted mb-0">All showroom vehicles with live stock and status.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="<?php echo base_url('/admin/reports/vehicles'); ?>" class="btn btn-ghost btn-sm">Open View</a>
                <a href="<?php echo base_url('/admin/reports/print?report=vehicles'); ?>" class="btn btn-ghost btn-sm">Print</a>
                <a href="<?php echo base_url('/admin/reports/export/pdf?report=vehicles'); ?>" class="btn btn-ghost btn-sm">PDF</a>
                <a href="<?php echo base_url('/admin/reports/export/excel?report=vehicles'); ?>" class="btn btn-accent btn-sm">Excel</a>
            </div>
        </div>
    </div>
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>Vehicle</th>
                <th>Category</th>
                <th>Stock</th>
                <th>Status</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($vehicles)): ?>
                <?php foreach ($vehicles as $vehicle): ?>
                    <tr>
                        <td class="fw-semibold"><?php echo e(trim((string)$vehicle['brand'] . ' ' . (string)$vehicle['model'])); ?> <?php echo e((string)$vehicle['year']); ?></td>
                        <td><?php echo e((string)($vehicle['category_name'] ?? 'Uncategorized')); ?></td>
                        <td><?php echo number_format((int)($vehicle['stock_qty'] ?? 1)); ?></td>
                        <td><span class="badge-status badge-confirmed"><?php echo e((string)($vehicle['status'] ?? 'active')); ?></span></td>
                        <td><?php echo e((string)($vehicle['currency'] ?? 'USD')); ?> <?php echo number_format((float)($vehicle['price'] ?? 0), 0); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">No vehicles found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="category-report" class="admin-table">
    <div style="padding: 20px; border-bottom: 1px solid var(--border-color);">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h5 style="margin-bottom: 0;">Category Report</h5>
                <p class="text-muted mb-0">Vehicle counts grouped by category.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="<?php echo base_url('/admin/reports/categories'); ?>" class="btn btn-ghost btn-sm">Open View</a>
                <a href="<?php echo base_url('/admin/reports/print?report=categories'); ?>" class="btn btn-ghost btn-sm">Print</a>
                <a href="<?php echo base_url('/admin/reports/export/pdf?report=categories'); ?>" class="btn btn-ghost btn-sm">PDF</a>
                <a href="<?php echo base_url('/admin/reports/export/excel?report=categories'); ?>" class="btn btn-accent btn-sm">Excel</a>
            </div>
        </div>
    </div>
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>Category</th>
                <th>Slug</th>
                <th>Vehicles</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td class="fw-semibold"><?php echo e((string)$category['name']); ?></td>
                        <td><code><?php echo e((string)$category['slug']); ?></code></td>
                        <td><?php echo number_format((int)($category['vehicle_count'] ?? 0)); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" class="text-center text-muted py-4">No categories found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
(() => {
    const ctx = document.getElementById('reportsChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chartLabels, JSON_UNESCAPED_SLASHES); ?>,
            datasets: [
                {
                    label: 'Revenue',
                    data: <?php echo json_encode($chartRevenue, JSON_UNESCAPED_SLASHES); ?>,
                    borderColor: '#00d4ff',
                    backgroundColor: 'rgba(0, 212, 255, 0.12)',
                    tension: 0.35,
                    fill: true
                },
                {
                    label: 'Sales Count',
                    data: <?php echo json_encode($chartCounts, JSON_UNESCAPED_SLASHES); ?>,
                    borderColor: '#00c853',
                    backgroundColor: 'rgba(0, 200, 83, 0.12)',
                    tension: 0.35,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { labels: { color: '#a8b5c3' } }
            },
            scales: {
                x: { ticks: { color: '#a8b5c3' }, grid: { display: false } },
                y: { ticks: { color: '#a8b5c3' }, grid: { color: 'rgba(255,255,255,0.06)' } }
            }
        }
    });
})();
</script>

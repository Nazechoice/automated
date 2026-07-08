<?php
$monthly = $reportData['monthly'] ?? [];
$summary = $reportData['summary'] ?? ['daily_sales' => 0, 'monthly_sales' => 0, 'yearly_sales' => 0];
$vehicles = $reportData['vehicles'] ?? [];
$categories = $reportData['categories'] ?? [];
$reportLabel = ucfirst((string)($reportType ?? 'all'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle ?? 'Printable Report'); ?></title>
    <style>
        body { font-family: Arial, sans-serif; padding: 24px; color: #111827; }
        h1, h2, h3 { margin: 0 0 12px; }
        .muted { color: #6b7280; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0 28px; }
        th, td { border: 1px solid #d1d5db; padding: 8px 10px; text-align: left; }
        th { background: #f3f4f6; }
        .summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin: 18px 0 24px; }
        .card { border: 1px solid #d1d5db; border-radius: 10px; padding: 12px; }
        @media print { .no-print { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 16px;">
        <button onclick="window.print()" style="padding: 10px 14px;">Print</button>
        <a href="<?php echo base_url('/admin/reports'); ?>" style="margin-left: 12px;">Back to reports</a>
    </div>

    <h1>AUTOMATES <?php echo e($reportLabel); ?> Report</h1>
    <p class="muted">Generated at <?php echo e(date('Y-m-d H:i:s')); ?></p>

    <?php if (in_array($reportType, ['all', 'sales'], true)): ?>
        <h2>Sales Summary</h2>
        <div class="summary">
            <div class="card"><strong>Daily</strong><br><?php echo number_format((float)($summary['daily_sales'] ?? 0), 2); ?></div>
            <div class="card"><strong>Monthly</strong><br><?php echo number_format((float)($summary['monthly_sales'] ?? 0), 2); ?></div>
            <div class="card"><strong>Yearly</strong><br><?php echo number_format((float)($summary['yearly_sales'] ?? 0), 2); ?></div>
        </div>

        <h3>Monthly Sales</h3>
        <table>
            <thead>
                <tr><th>Month</th><th>Count</th><th>Revenue</th></tr>
            </thead>
            <tbody>
                <?php if (!empty($monthly)): ?>
                    <?php foreach (array_reverse($monthly) as $row): ?>
                        <tr>
                            <td><?php echo e((string)$row['month']); ?></td>
                            <td><?php echo number_format((int)$row['sales_count']); ?></td>
                            <td><?php echo number_format((float)$row['revenue'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="3">No sales history yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if (in_array($reportType, ['all', 'vehicles'], true)): ?>
        <h2>Vehicle Report</h2>
        <table>
            <thead>
                <tr><th>Vehicle</th><th>Category</th><th>Stock</th><th>Status</th><th>Price</th></tr>
            </thead>
            <tbody>
                <?php if (!empty($vehicles)): ?>
                    <?php foreach ($vehicles as $row): ?>
                        <tr>
                            <td><?php echo e(trim((string)$row['brand'] . ' ' . (string)$row['model']) . ' ' . (string)$row['year']); ?></td>
                            <td><?php echo e((string)($row['category_name'] ?? 'Uncategorized')); ?></td>
                            <td><?php echo number_format((int)($row['stock_qty'] ?? 1)); ?></td>
                            <td><?php echo e((string)($row['status'] ?? 'active')); ?></td>
                            <td><?php echo e((string)($row['currency'] ?? 'USD')); ?> <?php echo number_format((float)($row['price'] ?? 0), 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5">No vehicles found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if (in_array($reportType, ['all', 'categories'], true)): ?>
        <h2>Category Report</h2>
        <table>
            <thead>
                <tr><th>Name</th><th>Slug</th><th>Vehicles</th></tr>
            </thead>
            <tbody>
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $row): ?>
                        <tr>
                            <td><?php echo e((string)$row['name']); ?></td>
                            <td><?php echo e((string)$row['slug']); ?></td>
                            <td><?php echo number_format((int)($row['vehicle_count'] ?? 0)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="3">No categories found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <script>
        window.addEventListener('load', () => window.print());
    </script>
</body>
</html>

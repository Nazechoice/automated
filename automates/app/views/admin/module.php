<?php
$moduleTitle = $moduleTitle ?? 'Module';
$moduleDescription = $moduleDescription ?? '';
$moduleHighlights = $moduleHighlights ?? [];
?>

<div class="admin-header">
    <div>
        <h1><i class="bi bi-grid-1x2"></i> <?php echo e($moduleTitle); ?></h1>
        <p class="text-muted mt-2"><?php echo e($moduleDescription); ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo base_url('/admin'); ?>" class="btn btn-ghost">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="<?php echo base_url('/admin/reports'); ?>" class="btn btn-accent">
            <i class="bi bi-file-earmark-bar-graph"></i> Reports
        </a>
    </div>
</div>

<div class="admin-stats">
    <div class="admin-stat-card">
        <div class="admin-stat-label">Workspace</div>
        <div class="admin-stat-value">Ready</div>
        <div class="admin-stat-trend">Connected to the admin layout</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Security</div>
        <div class="admin-stat-value">Protected</div>
        <div class="admin-stat-trend">Session + CSRF enabled</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Navigation</div>
        <div class="admin-stat-value">Live</div>
        <div class="admin-stat-trend">Sidebar link resolved successfully</div>
    </div>
</div>

<div class="admin-chart">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-3">
        <div>
            <h3 class="h5 mb-2">Module Summary</h3>
            <p class="text-muted mb-0">This section is wired and ready for the next CRUD layer.</p>
        </div>
        <span class="badge-status badge-confirmed">Functional</span>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="glass-card p-4 h-100">
                <h4 class="h6 mb-3">What this module will manage</h4>
                <ul class="mb-0 text-muted">
                    <li>Record handling and editing</li>
                    <li>Search, filters, and quick actions</li>
                    <li>Audit-friendly admin workflows</li>
                    <li>Theme-aware premium UI states</li>
                </ul>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="glass-card p-4 h-100">
                <h4 class="h6 mb-3">Quick Actions</h4>
                <div class="d-grid gap-2">
                    <a class="btn btn-primary btn-sm" href="<?php echo base_url('/admin'); ?>">Back to Overview</a>
                    <a class="btn btn-outline-light btn-sm" href="<?php echo base_url('/'); ?>">Open Public Site</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($moduleHighlights)): ?>
    <div class="admin-table">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Label</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($moduleHighlights as $label => $value): ?>
                    <tr>
                        <td><?php echo e((string)$label); ?></td>
                        <td><?php echo e((string)$value); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

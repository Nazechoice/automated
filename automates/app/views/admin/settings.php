<?php
$settings = $settings ?? \App\models\Setting::allMerged();
$successMessage = $successMessage ?? null;
$errors = $errors ?? [];
$logoPath = trim((string)($settings['logo_path'] ?? ''));
$logoUrl = $logoPath !== '' ? base_url($logoPath) : '';
?>

<div class="admin-header">
    <div>
        <h1><i class="bi bi-sliders2"></i> Settings</h1>
        <p class="text-muted mt-2">Save branding, contact details, footer content, and homepage copy directly to the database.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?php echo base_url('/admin/activity-logs'); ?>" class="btn btn-ghost">
            <i class="bi bi-clock-history"></i> Activity Logs
        </a>
        <a href="<?php echo base_url('/admin/profile'); ?>" class="btn btn-accent">
            <i class="bi bi-person-circle"></i> My Profile
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
        <div class="admin-stat-label">System Name</div>
        <div class="admin-stat-value"><?php echo e((string)($settings['system_name'] ?? 'Automates')); ?></div>
        <div class="admin-stat-trend">Loaded from the settings table</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Contact Email</div>
        <div class="admin-stat-value" style="font-size: 18px;"><?php echo e((string)($settings['contact_email'] ?? '')); ?></div>
        <div class="admin-stat-trend">Customer support inbox</div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-label">Homepage</div>
        <div class="admin-stat-value" style="font-size: 18px;"><?php echo e((string)($settings['homepage_title'] ?? '')); ?></div>
        <div class="admin-stat-trend">Hero content and branding</div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="admin-chart">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-3">
                <div>
                    <h3 class="h5 mb-1">Platform Settings</h3>
                    <p class="text-muted mb-0">These values are stored permanently and reused across the public site and dashboard layouts.</p>
                </div>
                <span class="badge-status badge-confirmed">Database backed</span>
            </div>

            <form method="post" action="<?php echo base_url('/admin/settings'); ?>" class="row g-3" enctype="multipart/form-data">
                <?php csrf_field(); ?>

                <div class="col-md-6">
                    <label class="form-label">System Name</label>
                    <input type="text" name="system_name" class="form-control glass-input" value="<?php echo e((string)($settings['system_name'] ?? '')); ?>" placeholder="Automobile Management System">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Contact Email</label>
                    <input type="email" name="contact_email" class="form-control glass-input" value="<?php echo e((string)($settings['contact_email'] ?? '')); ?>" placeholder="support@example.com">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Contact Phone</label>
                    <input type="text" name="contact_phone" class="form-control glass-input" value="<?php echo e((string)($settings['contact_phone'] ?? '')); ?>" placeholder="+234 800 000 0000">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Contact Address</label>
                    <input type="text" name="contact_address" class="form-control glass-input" value="<?php echo e((string)($settings['contact_address'] ?? '')); ?>" placeholder="Lagos, Nigeria">
                </div>

                <div class="col-md-8">
                    <label class="form-label">Logo Upload</label>
                    <input type="file" name="logo_file" class="form-control glass-input" accept="image/png,image/jpeg,image/webp,image/gif">
                    <div class="text-muted mt-2">Upload a new logo to replace the current brand image.</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Logo Preview</label>
                    <div class="glass-card p-3 d-flex align-items-center justify-content-center" style="min-height: 72px;">
                        <?php if ($logoUrl !== ''): ?>
                            <img src="<?php echo e($logoUrl); ?>" alt="System Logo" style="max-height: 48px; max-width: 100%; object-fit: contain;">
                        <?php else: ?>
                            <span class="text-muted">No logo uploaded</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label">Homepage Title</label>
                    <input type="text" name="homepage_title" class="form-control glass-input" value="<?php echo e((string)($settings['homepage_title'] ?? '')); ?>" placeholder="Discover Your Perfect Ride">
                </div>

                <div class="col-12">
                    <label class="form-label">Homepage Subtitle</label>
                    <textarea name="homepage_subtitle" rows="3" class="form-control glass-input" placeholder="Explore our exclusive collection of premium vehicles"><?php echo e((string)($settings['homepage_subtitle'] ?? '')); ?></textarea>
                </div>

                <div class="col-12">
                    <label class="form-label">Homepage About Text</label>
                    <textarea name="homepage_about" rows="3" class="form-control glass-input" placeholder="Short marketing summary shown on the homepage"><?php echo e((string)($settings['homepage_about'] ?? '')); ?></textarea>
                </div>

                <div class="col-12">
                    <label class="form-label">Footer Text</label>
                    <textarea name="footer_text" rows="3" class="form-control glass-input" placeholder="Footer copy shown on public pages"><?php echo e((string)($settings['footer_text'] ?? '')); ?></textarea>
                </div>

                <div class="col-12 d-flex align-items-center justify-content-between gap-3 flex-wrap">
                    <div class="text-muted small">
                        Saved values will be used by the homepage, public layouts, and dashboard branding.
                    </div>
                    <button type="submit" class="btn btn-accent">
                        <i class="bi bi-save"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="admin-chart">
            <h3 class="h5 mb-3">Live Preview</h3>
            <div class="glass-card p-3 mb-3">
                <div class="text-muted small text-uppercase mb-1">System</div>
                <div class="fw-semibold"><?php echo e((string)($settings['system_name'] ?? '')); ?></div>
            </div>
            <div class="glass-card p-3 mb-3">
                <div class="text-muted small text-uppercase mb-1">Hero Heading</div>
                <div class="fw-semibold"><?php echo e((string)($settings['homepage_title'] ?? '')); ?></div>
            </div>
            <div class="glass-card p-3 mb-3">
                <div class="text-muted small text-uppercase mb-1">Footer Copy</div>
                <div><?php echo e((string)($settings['footer_text'] ?? '')); ?></div>
            </div>
            <div class="glass-card p-3">
                <div class="text-muted small text-uppercase mb-1">Contact</div>
                <div class="mb-1"><?php echo e((string)($settings['contact_email'] ?? '')); ?></div>
                <div class="mb-1"><?php echo e((string)($settings['contact_phone'] ?? '')); ?></div>
                <div><?php echo e((string)($settings['contact_address'] ?? '')); ?></div>
            </div>
        </div>
    </div>
</div>

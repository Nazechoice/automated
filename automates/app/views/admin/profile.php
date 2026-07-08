<?php
$admin = $admin ?? null;
?>

<div class="admin-header">
    <div>
        <h1><i class="bi bi-person-circle"></i> Admin Profile</h1>
        <p class="text-muted mt-2">Signed-in administrator identity and security details.</p>
    </div>
    <a href="<?php echo base_url('/admin/settings'); ?>" class="btn btn-accent">
        <i class="bi bi-sliders2"></i> Settings
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="admin-chart">
            <h3 class="h5 mb-3">Profile Details</h3>
            <div class="glass-card p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="brand-splash" style="width:64px;height:64px;border-radius:20px;">A</div>
                    <div>
                        <h4 class="mb-1"><?php echo e((string)($admin->full_name ?? 'Administrator')); ?></h4>
                        <div class="text-muted"><?php echo e((string)($admin->email ?? '')); ?></div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted small text-uppercase mb-1">Role</div>
                        <div class="fw-semibold"><?php echo e(ucfirst((string)($admin->role ?? 'admin'))); ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small text-uppercase mb-1">Status</div>
                        <div class="fw-semibold"><?php echo e(ucfirst((string)($admin->status ?? 'active'))); ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small text-uppercase mb-1">Phone</div>
                        <div class="fw-semibold"><?php echo e((string)($admin->phone ?? 'N/A')); ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small text-uppercase mb-1">Account Type</div>
                        <div class="fw-semibold">System Administrator</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="admin-chart">
            <h3 class="h5 mb-3">Security</h3>
            <div class="d-grid gap-2">
                <div class="glass-card p-3">Password hashing enabled</div>
                <div class="glass-card p-3">CSRF protection enabled</div>
                <div class="glass-card p-3">Session authentication enabled</div>
            </div>
        </div>
    </div>
</div>

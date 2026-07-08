<?php
// Profile Content
$pageTitle = 'My Profile';
$user = $user ?? [];
?>

<div class="dashboard-header">
    <div>
        <h1><i class="bi bi-person"></i> My Profile</h1>
        <p class="text-muted mt-2">Manage your account information</p>
    </div>
</div>

<!-- Profile Sections -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 32px;">
    <!-- Main Profile Form -->
    <div class="glass-card">
        <h4 style="margin-bottom: 24px;">
            <i class="bi bi-person-fill"></i> Personal Information
        </h4>
        
        <form>
            <div class="mb-3">
                <label for="fullName" class="form-label text-muted" style="font-size: 12px; text-transform: uppercase;">Full Name</label>
            <input type="text" class="glass-input" id="fullName" placeholder="John Anderson" value="<?php echo e((string)($user['full_name'] ?? 'John Anderson')); ?>" style="width: 100%;">
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label text-muted" style="font-size: 12px; text-transform: uppercase;">Email Address</label>
            <input type="email" class="glass-input" id="email" placeholder="john@example.com" value="<?php echo e((string)($user['email'] ?? 'john@example.com')); ?>" style="width: 100%;">
            </div>
            
            <div class="mb-3">
                <label for="phone" class="form-label text-muted" style="font-size: 12px; text-transform: uppercase;">Phone Number</label>
            <input type="tel" class="glass-input" id="phone" placeholder="+1 (555) 123-4567" value="<?php echo e((string)($user['phone'] ?? '+1 (555) 123-4567')); ?>" style="width: 100%;">
            </div>
            
            <div class="mb-3">
                <label for="address" class="form-label text-muted" style="font-size: 12px; text-transform: uppercase;">Address</label>
            <input type="text" class="glass-input" id="address" placeholder="123 Main Street" value="123 Main Street, New York, NY 10001" style="width: 100%;">
            </div>
            
            <div class="mb-3">
                <label for="city" class="form-label text-muted" style="font-size: 12px; text-transform: uppercase;">City</label>
            <input type="text" class="glass-input" id="city" placeholder="New York" value="New York" style="width: 100%;">
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 24px;">
                <div>
                    <label for="state" class="form-label text-muted" style="font-size: 12px; text-transform: uppercase;">State</label>
                    <input type="text" class="glass-input" id="state" placeholder="NY" value="NY" style="width: 100%;">
                </div>
                <div>
                    <label for="zip" class="form-label text-muted" style="font-size: 12px; text-transform: uppercase;">ZIP Code</label>
                    <input type="text" class="glass-input" id="zip" placeholder="10001" value="10001" style="width: 100%;">
                </div>
            </div>
            
            <button type="submit" class="btn btn-accent btn-sm" style="width: 100%;">
                <i class="bi bi-check2"></i> Save Changes
            </button>
        </form>
    </div>
    
    <!-- Profile Avatar & Quick Stats -->
    <div>
        <div class="glass-card mb-3" style="text-align: center;">
            <div style="font-size: 64px; margin-bottom: 16px;">👤</div>
            <h5 style="margin-bottom: 8px;"><?php echo e((string)($user['full_name'] ?? 'John Anderson')); ?></h5>
            <p class="text-muted" style="margin-bottom: 0; font-size: 12px;">Member since 2024</p>
        </div>
        
        <div class="glass-card" style="text-align: center;">
            <h6 style="margin-bottom: 16px; color: var(--color-accent);">ACCOUNT STATUS</h6>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px; justify-content: center;">
                <div style="width: 12px; height: 12px; background: var(--color-success); border-radius: 50%;"></div>
                <span class="text-muted">Active</span>
            </div>
            <p class="text-muted" style="margin-bottom: 0; font-size: 12px;">Good standing</p>
        </div>
    </div>
</div>

<!-- Password Section -->
<div class="glass-card" style="margin-bottom: 32px;">
    <h4 style="margin-bottom: 24px;">
        <i class="bi bi-shield-lock"></i> Security & Password
    </h4>
    
    <form>
        <div class="mb-3">
            <label for="currentPassword" class="form-label text-muted" style="font-size: 12px; text-transform: uppercase;">Current Password</label>
            <input type="password" class="glass-input" id="currentPassword" placeholder="••••••••" style="width: 100%;">
        </div>
        
        <div class="mb-3">
            <label for="newPassword" class="form-label text-muted" style="font-size: 12px; text-transform: uppercase;">New Password</label>
            <input type="password" class="glass-input" id="newPassword" placeholder="••••••••" style="width: 100%;">
        </div>
        
        <div class="mb-3">
            <label for="confirmPassword" class="form-label text-muted" style="font-size: 12px; text-transform: uppercase;">Confirm Password</label>
            <input type="password" class="glass-input" id="confirmPassword" placeholder="••••••••" style="width: 100%;">
        </div>
        
        <button type="submit" class="btn btn-accent btn-sm">
            <i class="bi bi-check2"></i> Update Password
        </button>
    </form>
</div>

<!-- Preferences Section -->
<div class="glass-card" style="margin-bottom: 32px;">
    <h4 style="margin-bottom: 24px;">
        <i class="bi bi-sliders"></i> Preferences
    </h4>
    
    <div style="display: grid; gap: 16px;">
        <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; border-bottom: 1px solid var(--border-color);">
            <div>
                <strong>Email Notifications</strong>
                <p class="text-muted" style="margin-bottom: 0; font-size: 12px;">Receive updates about your bookings and offers</p>
            </div>
            <input type="checkbox" checked style="width: 20px; height: 20px;">
        </div>
        
        <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; border-bottom: 1px solid var(--border-color);">
            <div>
                <strong>SMS Notifications</strong>
                <p class="text-muted" style="margin-bottom: 0; font-size: 12px;">Get SMS updates about test drive confirmations</p>
            </div>
            <input type="checkbox" checked style="width: 20px; height: 20px;">
        </div>
        
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <strong>Marketing Emails</strong>
                <p class="text-muted" style="margin-bottom: 0; font-size: 12px;">Receive special offers and new vehicle announcements</p>
            </div>
            <input type="checkbox" style="width: 20px; height: 20px;">
        </div>
    </div>
</div>

<!-- Danger Zone -->
<div class="glass-card" style="border-color: var(--color-danger); border-width: 2px;">
    <h4 style="margin-bottom: 16px; color: var(--color-danger);">
        <i class="bi bi-exclamation-triangle"></i> Danger Zone
    </h4>
    
    <p class="text-muted" style="margin-bottom: 16px;">
        Permanently delete your account and all associated data. This action cannot be undone.
    </p>
    
    <button type="button" class="btn" style="background: rgba(255, 71, 87, 0.2); border: 1px solid var(--color-danger); color: var(--color-danger);">
        <i class="bi bi-trash"></i> Delete Account
    </button>
</div>

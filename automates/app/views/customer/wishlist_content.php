<?php
// Wishlist Content
$pageTitle = 'My Wishlist';
?>

<div class="dashboard-header">
    <div>
        <h1><i class="bi bi-heart-fill"></i> My Wishlist</h1>
        <p class="text-muted mt-2">Your saved favorite vehicles</p>
    </div>
    <div>
        <a href="<?php echo base_url('/'); ?>" class="btn btn-accent btn-sm">
            <i class="bi bi-plus"></i> Add More Vehicles
        </a>
    </div>
</div>

<!-- Wishlist Stats -->
<div class="dashboard-stats">
    <div class="stat-box">
        <div class="stat-box-value">3</div>
        <div class="stat-box-label">Total Items</div>
    </div>
    <div class="stat-box">
        <div class="stat-box-value">$289,497</div>
        <div class="stat-box-label">Total Value</div>
    </div>
</div>

<!-- Wishlist Items -->
<div class="dashboard-table">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Vehicle</th>
                <th>Brand & Model</th>
                <th>Year</th>
                <th>Price</th>
                <th>Type</th>
                <th>Added Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div style="width: 60px; height: 40px; background: linear-gradient(135deg, rgba(0, 212, 255, 0.2), rgba(26, 58, 82, 0.3)); border-radius: 6px;"></div>
                </td>
                <td>
                    <strong>BMW M5</strong>
                </td>
                <td>2024</td>
                <td><span class="text-accent">$89,999</span></td>
                <td><span class="badge badge-status" style="background: rgba(0, 200, 83, 0.2); color: var(--color-success);">Sports</span></td>
                <td><span class="text-muted">2 hours ago</span></td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-sm action-btn-small btn-accent" title="View">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm action-btn-small btn-danger" title="Remove">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            
            <tr>
                <td>
                    <div style="width: 60px; height: 40px; background: linear-gradient(135deg, rgba(0, 212, 255, 0.2), rgba(26, 58, 82, 0.3)); border-radius: 6px;"></div>
                </td>
                <td>
                    <strong>Mercedes-Benz S-Class</strong>
                </td>
                <td>2024</td>
                <td><span class="text-accent">$125,999</span></td>
                <td><span class="badge badge-status" style="background: rgba(255, 165, 0, 0.2); color: var(--color-warning);">Luxury</span></td>
                <td><span class="text-muted">1 day ago</span></td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-sm action-btn-small btn-accent" title="View">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm action-btn-small btn-danger" title="Remove">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            
            <tr>
                <td>
                    <div style="width: 60px; height: 40px; background: linear-gradient(135deg, rgba(0, 212, 255, 0.2), rgba(26, 58, 82, 0.3)); border-radius: 6px;"></div>
                </td>
                <td>
                    <strong>Audi A8</strong>
                </td>
                <td>2024</td>
                <td><span class="text-accent">$95,499</span></td>
                <td><span class="badge badge-status" style="background: rgba(0, 212, 255, 0.2); color: var(--color-accent);">Sedan</span></td>
                <td><span class="text-muted">3 days ago</span></td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-sm action-btn-small btn-accent" title="View">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm action-btn-small btn-danger" title="Remove">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Empty State (uncomment when no items) -->
<!-- <div class="glass-card text-center" style="padding: 60px 24px;">
    <div style="font-size: 48px; margin-bottom: 16px;">💔</div>
    <h4>Your wishlist is empty</h4>
    <p class="text-muted" style="margin-bottom: 24px;">Start adding your favorite vehicles to your wishlist</p>
    <a href="<?php echo base_url('/'); ?>" class="btn btn-accent">
        <i class="bi bi-search"></i> Browse Vehicles
    </a>
</div> -->

<?php
$currentAdminPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
$layoutSettings = \App\models\Setting::allMerged();
$layoutSystemName = (string)($layoutSettings['system_name'] ?? 'AUTOMATES');
$layoutLogoPath = trim((string)($layoutSettings['logo_path'] ?? ''));
$layoutLogoUrl = $layoutLogoPath !== '' ? base_url($layoutLogoPath) : '';
$layoutUser = \App\lib\Auth::user();
$layoutNotificationCount = $layoutUser ? \App\models\Notification::unreadCount((int)$layoutUser->id) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle ?? 'Admin Dashboard'); ?> | AUTOMATES</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Chart.js for analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.0.0/dist/chart.min.js"></script>
    
    <!-- Modern Theme CSS -->
    <link rel="stylesheet" href="<?php echo base_url('assets/css/modern.css'); ?>">
    
    <style>
        .admin-container {
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: 0;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            background: var(--bg-glass);
            backdrop-filter: blur(10px);
            border-right: 1px solid var(--border-color);
            padding: 24px 0;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }
        
        .admin-sidebar-brand {
            padding: 0 24px 32px;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 24px;
        }
        
        .admin-sidebar-brand a {
            font-size: 18px;
            font-weight: 800;
            background: var(--gradient-text);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .admin-nav {
            list-style: none;
            padding: 0 12px;
        }
        
        .admin-nav-section {
            margin-bottom: 24px;
        }
        
        .admin-nav-section-title {
            padding: 0 16px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-muted);
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }
        
        .admin-nav-item {
            margin-bottom: 6px;
        }
        
        .admin-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            border-radius: 8px;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 14px;
        }
        
        .admin-nav-link:hover,
        .admin-nav-link.active {
            background: rgba(0, 212, 255, 0.1);
            color: var(--color-accent);
            border-left: 3px solid var(--color-accent);
            padding-left: 13px;
        }
        
        .admin-nav-link i {
            font-size: 16px;
            width: 20px;
        }
        
        .admin-content {
            padding: 32px;
            overflow-y: auto;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .admin-header h1 {
            margin: 0;
            font-size: 32px;
        }
        
        .admin-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 32px;
        }
        
        .admin-stat-card {
            background: var(--bg-glass);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            padding: 20px;
            border-radius: 12px;
            position: relative;
            overflow: hidden;
        }
        
        .admin-stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(0, 212, 255, 0.05), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .admin-stat-card:hover::before {
            opacity: 1;
        }
        
        .admin-stat-value {
            font-size: 28px;
            font-weight: 800;
            color: var(--color-accent);
            margin-bottom: 8px;
        }
        
        .admin-stat-label {
            font-size: 13px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        
        .admin-stat-trend {
            font-size: 12px;
            color: var(--color-success);
        }
        
        .admin-chart {
            background: var(--bg-glass);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 32px;
        }
        
        .admin-table {
            background: var(--bg-glass);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 32px;
        }
        
        .admin-table table {
            margin: 0;
            color: var(--text-primary);
        }
        
        .admin-table th {
            background: rgba(0, 212, 255, 0.1);
            border-color: var(--border-color);
            font-weight: 700;
            color: var(--color-accent);
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }
        
        .admin-table td {
            border-color: var(--border-color);
            vertical-align: middle;
            padding: 16px !important;
        }
        
        .admin-table tbody tr:hover {
            background: rgba(0, 212, 255, 0.05);
        }
        
        @media (max-width: 768px) {
            .admin-container {
                grid-template-columns: 1fr;
            }
            
            .admin-sidebar {
                position: fixed;
                left: -260px;
                width: 260px;
                height: 100vh;
                z-index: 1000;
                transition: left 0.3s ease;
            }
            
            .admin-sidebar.show {
                left: 0;
            }
            
            .admin-content {
                padding: 20px;
            }
            
            .admin-stats {
                grid-template-columns: 1fr;
            }
            
            .admin-header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar sticky-top" style="z-index: 999;">
        <div class="container-fluid px-4">
            <div class="d-flex gap-3 align-items-center">
                <button class="btn btn-ghost d-md-none" id="sidebarToggle" type="button">
                    <i class="bi bi-list"></i>
                </button>
                <span>Admin Control Panel</span>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <button class="theme-toggle" id="themeToggle" type="button">
                    <i class="bi bi-moon-fill"></i>
                </button>
                
                <div class="dropdown">
                    <button class="btn btn-ghost" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-shield-lock"></i> Admin
                        <?php if ($layoutNotificationCount > 0): ?>
                            <span class="badge bg-danger ms-2"><?php echo number_format($layoutNotificationCount); ?></span>
                        <?php endif; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" style="background: var(--bg-glass); border: 1px solid var(--border-color);">
                        <li><a class="dropdown-item" href="<?php echo base_url('/admin/profile'); ?>">My Profile</a></li>
                        <li><a class="dropdown-item" href="<?php echo base_url('/admin/settings'); ?>">Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="<?php echo base_url('/logout'); ?>">
                                <?php csrf_field(); ?>
                                <button type="submit" class="dropdown-item">Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Admin Container -->
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="admin-sidebar-brand">
                <a href="<?php echo base_url('/'); ?>">
                    <?php if ($layoutLogoUrl !== ''): ?>
                        <img src="<?php echo e($layoutLogoUrl); ?>" alt="<?php echo e($layoutSystemName); ?>" style="width: 28px; height: 28px; object-fit: contain;">
                    <?php else: ?>
                        <i class="bi bi-lightning-charge-fill"></i>
                    <?php endif; ?>
                    <span><?php echo e($layoutSystemName); ?></span>
                </a>
            </div>
            
            <ul class="admin-nav">
                <!-- Main Section -->
                <li class="admin-nav-section">
                    <div class="admin-nav-section-title">Main</div>
                    <li class="admin-nav-item">
                        <a href="<?php echo base_url('/admin'); ?>" class="admin-nav-link <?php echo ($currentAdminPath === '/automates/admin' || $currentAdminPath === '/admin' ? 'active' : ''); ?>">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                </li>
                
                <!-- Management Section -->
                <li class="admin-nav-section">
                    <div class="admin-nav-section-title">Management</div>
                    
                    <li class="admin-nav-item">
                        <a href="<?php echo base_url('/admin/vehicles'); ?>" class="admin-nav-link <?php echo (str_starts_with($currentAdminPath, '/automates/admin/vehicles') || str_starts_with($currentAdminPath, '/admin/vehicles') ? 'active' : ''); ?>">
                            <i class="bi bi-car-front"></i>
                            <span>Vehicles</span>
                        </a>
                    </li>
                    
                    <li class="admin-nav-item">
                        <a href="<?php echo base_url('/admin/categories'); ?>" class="admin-nav-link <?php echo (str_contains($currentAdminPath, '/admin/categories') ? 'active' : ''); ?>">
                            <i class="bi bi-tag"></i>
                            <span>Categories</span>
                        </a>
                    </li>
                    
                    <li class="admin-nav-item">
                        <a href="<?php echo base_url('/admin/customers'); ?>" class="admin-nav-link <?php echo (str_contains($currentAdminPath, '/admin/customers') ? 'active' : ''); ?>">
                            <i class="bi bi-people"></i>
                            <span>Customers</span>
                        </a>
                    </li>
                    
                    <li class="admin-nav-item">
                        <a href="<?php echo base_url('/admin/bookings'); ?>" class="admin-nav-link <?php echo (str_contains($currentAdminPath, '/admin/bookings') ? 'active' : ''); ?>">
                            <i class="bi bi-calendar-check"></i>
                            <span>Bookings</span>
                        </a>
                    </li>
                    
                    <li class="admin-nav-item">
                        <a href="<?php echo base_url('/admin/test-drives'); ?>" class="admin-nav-link <?php echo (str_contains($currentAdminPath, '/admin/test-drives') ? 'active' : ''); ?>">
                            <i class="bi bi-file-earmark"></i>
                            <span>Test Drives</span>
                        </a>
                    </li>
                    
                    <li class="admin-nav-item">
                        <a href="<?php echo base_url('/admin/inventory'); ?>" class="admin-nav-link <?php echo (str_contains($currentAdminPath, '/admin/inventory') ? 'active' : ''); ?>">
                            <i class="bi bi-boxes"></i>
                            <span>Inventory</span>
                        </a>
                    </li>
                </li>
                
                <!-- Analytics Section -->
                <li class="admin-nav-section">
                    <div class="admin-nav-section-title">Analytics</div>
                    
                    <li class="admin-nav-item">
                        <a href="<?php echo base_url('/admin/sales'); ?>" class="admin-nav-link <?php echo (str_contains($currentAdminPath, '/admin/sales') ? 'active' : ''); ?>">
                            <i class="bi bi-graph-up"></i>
                            <span>Sales</span>
                        </a>
                    </li>
                    
                    <li class="admin-nav-item">
                        <a href="<?php echo base_url('/admin/reports'); ?>" class="admin-nav-link <?php echo (str_contains($currentAdminPath, '/admin/reports') ? 'active' : ''); ?>">
                            <i class="bi bi-file-text"></i>
                            <span>Reports</span>
                        </a>
                    </li>
                </li>
                
                <!-- System Section -->
                <li class="admin-nav-section">
                    <div class="admin-nav-section-title">System</div>
                    
                    <li class="admin-nav-item">
                        <a href="<?php echo base_url('/admin/users'); ?>" class="admin-nav-link <?php echo (str_contains($currentAdminPath, '/admin/users') ? 'active' : ''); ?>">
                            <i class="bi bi-person-gear"></i>
                            <span>Users</span>
                        </a>
                    </li>
                    
                    <li class="admin-nav-item">
                        <a href="<?php echo base_url('/admin/settings'); ?>" class="admin-nav-link <?php echo (str_contains($currentAdminPath, '/admin/settings') ? 'active' : ''); ?>">
                            <i class="bi bi-sliders"></i>
                            <span>Settings</span>
                        </a>
                    </li>

                    <li class="admin-nav-item">
                        <a href="<?php echo base_url('/admin/activity-logs'); ?>" class="admin-nav-link <?php echo (str_contains($currentAdminPath, '/admin/activity-logs') ? 'active' : ''); ?>">
                            <i class="bi bi-clock-history"></i>
                            <span>Activity Logs</span>
                        </a>
                    </li>

                    <li class="admin-nav-item">
                        <a href="<?php echo base_url('/admin/notifications'); ?>" class="admin-nav-link <?php echo (str_contains($currentAdminPath, '/admin/notifications') ? 'active' : ''); ?>">
                            <i class="bi bi-bell"></i>
                            <span>Notifications</span>
                            <?php if ($layoutNotificationCount > 0): ?>
                                <span class="badge bg-danger ms-auto"><?php echo number_format($layoutNotificationCount); ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </li>
                
                <!-- Divider -->
                <li style="border-top: 1px solid var(--border-color); margin-top: 24px; padding-top: 24px; margin-bottom: 12px;">
                    <form method="POST" action="<?php echo base_url('/logout'); ?>" class="mb-2">
                        <?php csrf_field(); ?>
                        <button type="submit" class="admin-nav-link w-100" style="background: transparent; border: 0; text-align: left;">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                    <a href="<?php echo base_url('/'); ?>" class="admin-nav-link">
                        <i class="bi bi-house"></i>
                        <span>Back to Site</span>
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <?php
            if (isset($contentView) && file_exists($contentView)) {
                include $contentView;
            } else {
                include __DIR__ . '/../admin/dashboard.php';
            }
            ?>
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        
        const storedTheme = localStorage.getItem('theme');
        const currentTheme = storedTheme === 'light-mode' ? 'light' : (storedTheme === 'dark-mode' ? 'dark' : (storedTheme || 'dark'));
        html.setAttribute('data-theme', currentTheme);
        html.classList.toggle('light-mode', currentTheme === 'light');
        updateThemeIcon();
        
        themeToggle.addEventListener('click', () => {
            const theme = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
            html.setAttribute('data-theme', theme);
            html.classList.toggle('light-mode', theme === 'light');
            localStorage.setItem('theme', theme);
            updateThemeIcon();
        });
        
        function updateThemeIcon() {
            const icon = themeToggle.querySelector('i');
            if (html.getAttribute('data-theme') === 'light') {
                icon.classList.remove('bi-moon-fill');
                icon.classList.add('bi-sun-fill');
            } else {
                icon.classList.remove('bi-sun-fill');
                icon.classList.add('bi-moon-fill');
            }
        }
        
        // Sidebar Toggle (Mobile)
        const sidebarToggle = document.getElementById('sidebarToggle');
        const adminSidebar = document.getElementById('adminSidebar');
        
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                adminSidebar.classList.toggle('show');
            });
        }
        
        // Close sidebar when a link is clicked on mobile
        document.querySelectorAll('.admin-nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    adminSidebar.classList.remove('show');
                }
            });
        });
    </script>
</body>
</html>

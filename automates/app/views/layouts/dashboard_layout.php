<?php
$layoutSettings = \App\models\Setting::allMerged();
$layoutSystemName = (string)($layoutSettings['system_name'] ?? 'AUTOMATES');
$layoutLogoPath = trim((string)($layoutSettings['logo_path'] ?? ''));
$layoutLogoUrl = $layoutLogoPath !== '' ? base_url($layoutLogoPath) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle ?? 'Dashboard'); ?> | AUTOMATES</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Modern Theme CSS -->
    <link rel="stylesheet" href="<?php echo base_url('assets/css/modern.css'); ?>">
    
    <style>
        .dashboard-container {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 0;
            min-height: 100vh;
        }
        
        .dashboard-sidebar {
            background: var(--bg-glass);
            backdrop-filter: blur(10px);
            border-right: 1px solid var(--border-color);
            padding: 24px 0;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }
        
        .dashboard-sidebar-brand {
            padding: 0 24px 32px;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 24px;
        }
        
        .dashboard-sidebar-brand a {
            font-size: 20px;
            font-weight: 800;
            background: var(--gradient-text);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .dashboard-nav {
            list-style: none;
            padding: 0 12px;
        }
        
        .dashboard-nav-item {
            margin-bottom: 8px;
        }
        
        .dashboard-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 8px;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .dashboard-nav-link:hover,
        .dashboard-nav-link.active {
            background: rgba(0, 212, 255, 0.1);
            color: var(--color-accent);
            border-left: 3px solid var(--color-accent);
            padding-left: 13px;
        }
        
        .dashboard-nav-link i {
            font-size: 18px;
            width: 24px;
        }
        
        .dashboard-content {
            padding: 32px;
            overflow-y: auto;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .dashboard-header h1 {
            margin: 0;
            font-size: 32px;
        }
        
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }
        
        .stat-box {
            background: var(--bg-glass);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            padding: 24px;
            border-radius: 12px;
            text-align: center;
        }
        
        .stat-box-value {
            font-size: 32px;
            font-weight: 800;
            color: var(--color-accent);
            margin-bottom: 8px;
        }
        
        .stat-box-label {
            font-size: 14px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .dashboard-table {
            background: var(--bg-glass);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 32px;
        }
        
        .dashboard-table table {
            margin: 0;
            color: var(--text-primary);
        }
        
        .dashboard-table th {
            background: rgba(0, 212, 255, 0.1);
            border-color: var(--border-color);
            font-weight: 700;
            color: var(--color-accent);
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
        
        .dashboard-table td {
            border-color: var(--border-color);
            vertical-align: middle;
        }
        
        .dashboard-table tbody tr:hover {
            background: rgba(0, 212, 255, 0.05);
        }
        
        .badge-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        
        .badge-pending {
            background: rgba(255, 165, 0, 0.2);
            color: var(--color-warning);
        }
        
        .badge-confirmed {
            background: rgba(0, 200, 83, 0.2);
            color: var(--color-success);
        }
        
        .badge-completed {
            background: rgba(0, 212, 255, 0.2);
            color: var(--color-accent);
        }
        
        .badge-cancelled {
            background: rgba(255, 71, 87, 0.2);
            color: var(--color-danger);
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .action-btn-small {
            padding: 6px 12px;
            font-size: 11px;
            border-radius: 6px;
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
            
            .dashboard-sidebar {
                position: fixed;
                left: -280px;
                width: 280px;
                height: 100vh;
                z-index: 1000;
                transition: left 0.3s ease;
            }
            
            .dashboard-sidebar.show {
                left: 0;
            }
            
            .dashboard-content {
                padding: 20px;
            }
            
            .dashboard-stats {
                grid-template-columns: 1fr;
            }
            
            .dashboard-header h1 {
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
                <span>Customer Dashboard</span>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <button class="theme-toggle" id="themeToggle" type="button">
                    <i class="bi bi-moon-fill"></i>
                </button>
                
                <div class="dropdown">
                    <button class="btn btn-ghost" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?php echo e($user['full_name'] ?? 'User'); ?>
                        <?php if (!empty($user['notification_count'])): ?>
                            <span class="badge bg-danger ms-2"><?php echo number_format((int)$user['notification_count']); ?></span>
                        <?php endif; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" style="background: var(--bg-glass); border: 1px solid var(--border-color);">
                        <li><a class="dropdown-item" href="<?php echo base_url('/profile'); ?>">My Profile</a></li>
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

    <!-- Dashboard Container -->
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="dashboard-sidebar" id="dashboardSidebar">
            <div class="dashboard-sidebar-brand">
                <a href="<?php echo base_url('/'); ?>">
                    <?php if ($layoutLogoUrl !== ''): ?>
                        <img src="<?php echo e($layoutLogoUrl); ?>" alt="<?php echo e($layoutSystemName); ?>" style="width: 28px; height: 28px; object-fit: contain;">
                    <?php else: ?>
                        <i class="bi bi-lightning-charge-fill"></i>
                    <?php endif; ?>
                    <span><?php echo e($layoutSystemName); ?></span>
                </a>
            </div>
            
            <ul class="dashboard-nav">
                <li class="dashboard-nav-item">
                    <a href="<?php echo base_url('/dashboard'); ?>" class="dashboard-nav-link <?php echo (basename($_SERVER['REQUEST_URI']) === 'dashboard' ? 'active' : ''); ?>">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <li class="dashboard-nav-item">
                    <a href="<?php echo base_url('/wishlist'); ?>" class="dashboard-nav-link <?php echo (basename($_SERVER['REQUEST_URI']) === 'wishlist' ? 'active' : ''); ?>">
                        <i class="bi bi-heart"></i>
                        <span>My Wishlist</span>
                    </a>
                </li>
                
                <li class="dashboard-nav-item">
                    <a href="<?php echo base_url('/bookings'); ?>" class="dashboard-nav-link <?php echo (basename($_SERVER['REQUEST_URI']) === 'bookings' ? 'active' : ''); ?>">
                        <i class="bi bi-calendar-check"></i>
                        <span>My Bookings</span>
                    </a>
                </li>
                
                <li class="dashboard-nav-item">
                    <a href="<?php echo base_url('/requests'); ?>" class="dashboard-nav-link <?php echo (basename($_SERVER['REQUEST_URI']) === 'requests' ? 'active' : ''); ?>">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>Test Drives</span>
                    </a>
                </li>
                
                <li class="dashboard-nav-item">
                    <a href="<?php echo base_url('/profile'); ?>" class="dashboard-nav-link <?php echo (basename($_SERVER['REQUEST_URI']) === 'profile' ? 'active' : ''); ?>">
                        <i class="bi bi-person"></i>
                        <span>My Profile</span>
                    </a>
                </li>

                <li class="dashboard-nav-item">
                    <a href="<?php echo base_url('/notifications'); ?>" class="dashboard-nav-link <?php echo (basename($_SERVER['REQUEST_URI']) === 'notifications' ? 'active' : ''); ?>">
                        <i class="bi bi-bell"></i>
                        <span>Notifications</span>
                        <?php if (!empty($user['notification_count'])): ?>
                            <span class="badge bg-danger ms-auto"><?php echo number_format((int)$user['notification_count']); ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                
                <li class="dashboard-nav-item" style="border-top: 1px solid var(--border-color); margin-top: 24px; padding-top: 24px;">
                    <a href="<?php echo base_url('/'); ?>" class="dashboard-nav-link">
                        <i class="bi bi-house"></i>
                        <span>Back to Home</span>
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="dashboard-content">
            <?php
            if (isset($contentView) && file_exists($contentView)) {
                include $contentView;
            } else {
                include __DIR__ . '/../customer/dashboard_overview.php';
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
        const dashboardSidebar = document.getElementById('dashboardSidebar');
        
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                dashboardSidebar.classList.toggle('show');
            });
        }
        
        // Close sidebar when a link is clicked on mobile
        document.querySelectorAll('.dashboard-nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    dashboardSidebar.classList.remove('show');
                }
            });
        });
    </script>
</body>
</html>

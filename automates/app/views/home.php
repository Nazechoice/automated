<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Modern Theme CSS -->
    <link rel="stylesheet" href="<?php echo base_url('assets/css/modern.css'); ?>">
    
    <style>
        /* Additional animations and effects */
        .vehicle-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
        }
        
        .testimonial-card {
            background: var(--bg-glass);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            padding: 32px;
            border-radius: 12px;
            text-align: center;
        }
        
        .testimonial-stars {
            color: var(--color-accent);

            margin-bottom: 16px;
        }
        
        .counter {
            font-size: 36px;
            font-weight: 800;
            color: var(--color-accent);
        }
        
        .feature-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, rgba(0, 212, 255, 0.1), rgba(26, 58, 82, 0.2));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
    <?php
    $categories = $categories ?? [];
    $siteSettings = \App\models\Setting::allMerged();
    $systemName = (string)($siteSettings['system_name'] ?? 'AUTOMATES');
    $logoPath = trim((string)($siteSettings['logo_path'] ?? ''));
    $logoUrl = $logoPath !== '' ? base_url($logoPath) : '';
    ?>
    <!-- ============================================
         NAVBAR
         ============================================ -->
    <nav class="navbar sticky-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="<?php echo base_url('/'); ?>">
                <?php if ($logoUrl !== ''): ?>
                    <img src="<?php echo e($logoUrl); ?>" alt="<?php echo e($systemName); ?>" style="width: 28px; height: 28px; object-fit: contain; margin-right: 8px;">
                <?php else: ?>
                    <i class="bi bi-lightning-charge-fill"></i>
                <?php endif; ?>
                <?php echo e($systemName); ?>
            </a>
            
            <div class="d-flex align-items-center gap-3">
                <button class="theme-toggle" id="themeToggle" type="button">
                    <i class="bi bi-moon-fill"></i>
                </button>
                
                <?php if ($isAuthenticated): ?>
                    <div class="navbar-nav">
                        <a href="<?php echo base_url('/dashboard'); ?>" class="nav-link">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                        <form method="POST" action="<?php echo base_url('/logout'); ?>" style="display: inline;">
                            <?php csrf_field(); ?>
                            <button type="submit" class="btn btn-ghost btn-sm">Logout</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="navbar-nav">
                        <a href="<?php echo base_url('/login'); ?>" class="nav-link">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </a>
                        <a href="<?php echo base_url('/register'); ?>" class="btn btn-accent btn-sm">Register</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- ============================================
         HERO SECTION
         ============================================ -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1><?php echo e((string)($siteSettings['homepage_title'] ?? 'Discover Your Perfect Ride')); ?></h1>
                <p><?php echo e((string)($siteSettings['homepage_subtitle'] ?? 'Explore our exclusive collection of premium vehicles handpicked for luxury and performance')); ?></p>
                
                <div class="hero-search">
                    <form method="GET" action="<?php echo base_url('/api/vehicles/search'); ?>" class="hero-search-form">
                        <div class="search-group">
                            <label for="brand">Brand</label>
                            <input type="text" id="brand" name="brand" class="glass-input" placeholder="e.g., BMW, Mercedes">
                        </div>
                        
                        <div class="search-group">
                            <label for="fuel">Fuel Type</label>
                            <select id="fuel" name="fuel" class="glass-input">
                                <option value="">All Types</option>
                                <option value="petrol">Petrol</option>
                                <option value="diesel">Diesel</option>
                                <option value="hybrid">Hybrid</option>
                                <option value="electric">Electric</option>
                            </select>
                        </div>
                        
                        <div class="search-group">
                            <label for="transmission">Transmission</label>
                            <select id="transmission" name="transmission" class="glass-input">
                                <option value="">All</option>
                                <option value="manual">Manual</option>
                                <option value="automatic">Automatic</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-accent">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </form>
                </div>
                
                <div class="hero-stats">
                    <div class="stat-card">
                        <div class="stat-number">500+</div>
                        <div class="stat-label">Premium Vehicles</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">25K+</div>
                        <div class="stat-label">Happy Customers</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">15+</div>
                        <div class="stat-label">Years Experience</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================
         FEATURED VEHICLES
         ============================================ -->
    <section class="section">
        <div class="container">
            <div class="section-title d-flex justify-content-between align-items-end flex-wrap gap-3">
                <div>
                    <h2>Featured Vehicles</h2>
                    <p>Handpicked premium vehicles featuring the latest models and exclusive deals</p>
                </div>
                <a href="<?php echo base_url('/vehicles'); ?>" class="btn btn-ghost">
                    <i class="bi bi-grid"></i> Browse All Vehicles
                </a>
            </div>
            
            <div class="vehicle-grid">
                <?php if (!empty($featuredVehicles)): ?>
                    <?php foreach ($featuredVehicles as $vehicle): ?>
                        <?php
                        $vehicleTitle = trim((string)$vehicle['brand'] . ' ' . (string)$vehicle['model']);
                        $badge = !empty($vehicle['featured']) ? 'Featured' : 'New Arrival';
                        $image = base_url((string)$vehicle['cover']);
                        $mileage = !empty($vehicle['mileage_km']) ? number_format((float)$vehicle['mileage_km']) . ' km' : 'Brand new';
                        ?>
                        <div class="vehicle-card">
                            <div class="vehicle-card-image">
                                <img src="<?php echo e($image); ?>" alt="<?php echo e($vehicleTitle); ?>">
                                <span class="vehicle-card-badge"><?php echo e($badge); ?></span>
                                <div class="vehicle-card-actions">
                                    <button class="action-btn wishlist-btn" title="Add to Wishlist" data-vehicle-id="<?php echo (int)$vehicle['id']; ?>">
                                        <i class="bi bi-heart"></i>
                                    </button>
                                    <button class="action-btn compare-btn" title="Compare" data-vehicle-id="<?php echo (int)$vehicle['id']; ?>">
                                        <i class="bi bi-shuffle"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="vehicle-card-content">
                                <h4><?php echo e($vehicleTitle . ' ' . (string)$vehicle['year']); ?></h4>
                                <div class="vehicle-card-meta">
                                    <span><i class="bi bi-speedometer"></i> <?php echo e($mileage); ?></span>
                                    <span><i class="bi bi-fuel-pump"></i> <?php echo e(ucfirst((string)$vehicle['fuel_type'])); ?></span>
                                    <span><i class="bi bi-gear"></i> <?php echo e(ucfirst((string)$vehicle['transmission'])); ?></span>
                                </div>
                                <div class="vehicle-card-meta">
                                    <span><i class="bi bi-tag"></i> <?php echo e((string)($vehicle['category_name'] ?? 'Luxury')); ?></span>
                                    <span><i class="bi bi-patch-check"></i> <?php echo e((string)($vehicle['currency'] ?? 'USD')); ?></span>
                                </div>
                                <div class="vehicle-card-price"><?php echo e((string)($vehicle['currency'] ?? 'USD')); ?> <?php echo number_format((float)$vehicle['price']); ?></div>
                                <a href="<?php echo e(base_url('/vehicle?id=' . (int)$vehicle['id'])); ?>" class="btn btn-primary btn-sm vehicle-card-btn">View Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="glass-card text-center" style="grid-column: 1 / -1; padding: 40px;">
                        <i class="bi bi-car-front-fill" style="font-size: 40px; color: var(--color-accent);"></i>
                        <h4 class="mt-3 mb-2">No live vehicles yet</h4>
                        <p class="text-muted mb-0">Add active cars in the admin panel and they will appear here automatically.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ============================================
         LATEST ARRIVALS
         ============================================ -->
    <section class="section" style="background: linear-gradient(135deg, rgba(0, 212, 255, 0.04) 0%, rgba(26, 58, 82, 0.05) 100%);">
        <div class="container">
            <div class="section-title d-flex justify-content-between align-items-end flex-wrap gap-3">
                <div>
                    <h2>Latest Arrivals</h2>
                    <p>New active vehicles are pulled from the database as soon as the admin creates them.</p>
                </div>
                <a href="<?php echo base_url('/dashboard'); ?>" class="btn btn-ghost">
                    <i class="bi bi-speedometer2"></i> Open Dashboard
                </a>
            </div>

            <div class="vehicle-grid">
                <?php if (!empty($latestVehicles)): ?>
                    <?php foreach ($latestVehicles as $vehicle): ?>
                        <?php
                        $vehicleTitle = trim((string)$vehicle['brand'] . ' ' . (string)$vehicle['model']);
                        $image = base_url((string)$vehicle['cover']);
                        ?>
                        <div class="vehicle-card">
                            <div class="vehicle-card-image">
                                <img src="<?php echo e($image); ?>" alt="<?php echo e($vehicleTitle); ?>">
                                <span class="vehicle-card-badge">Latest</span>
                            </div>
                            <div class="vehicle-card-content">
                                <h4><?php echo e($vehicleTitle . ' ' . (string)$vehicle['year']); ?></h4>
                                <div class="vehicle-card-meta">
                                    <span><i class="bi bi-tag"></i> <?php echo e((string)($vehicle['category_name'] ?? 'Luxury')); ?></span>
                                    <span><i class="bi bi-speedometer"></i> <?php echo !empty($vehicle['mileage_km']) ? e(number_format((float)$vehicle['mileage_km']) . ' km') : 'Brand new'; ?></span>
                                </div>
                                <div class="vehicle-card-price"><?php echo e((string)($vehicle['currency'] ?? 'USD')); ?> <?php echo number_format((float)$vehicle['price']); ?></div>
                                <a href="<?php echo e(base_url('/vehicle?id=' . (int)$vehicle['id'])); ?>" class="btn btn-primary btn-sm vehicle-card-btn">View Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="glass-card text-center" style="grid-column: 1 / -1; padding: 40px;">
                        <i class="bi bi-clock-history" style="font-size: 40px; color: var(--color-accent);"></i>
                        <h4 class="mt-3 mb-2">No latest arrivals yet</h4>
                        <p class="text-muted mb-0">As soon as the admin adds a live vehicle, it will appear here automatically.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ============================================
         CATEGORIES
         ============================================ -->
    <section class="section" style="background: linear-gradient(135deg, rgba(0, 212, 255, 0.05) 0%, rgba(26, 58, 82, 0.05) 100%);">
        <div class="container">
            <div class="section-title">
                <h2>Browse by Category</h2>
                <p>Find the perfect vehicle from our curated collection</p>
            </div>
            
            <div class="grid grid-3">
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $category): ?>
                        <?php
                        $slug = strtolower((string)($category['slug'] ?? ''));
                        $categoryIcon = match ($slug) {
                            'suv' => 'bi-truck',
                            'sedan' => 'bi-car-front',
                            'truck' => 'bi-box-seam',
                            'luxury' => 'bi-gem',
                            'sports' => 'bi-lightning-charge',
                            'electric' => 'bi-battery-charging',
                            default => 'bi-tag',
                        };
                        $categoryName = (string)($category['name'] ?? 'Category');
                        $categoryCount = (int)($category['vehicle_count'] ?? 0);
                        ?>
                        <a href="<?php echo base_url('/vehicles?category=' . rawurlencode((string)$category['slug'])); ?>" class="category-card" style="text-decoration: none; color: inherit;">
                            <div class="category-card-icon"><i class="bi <?php echo e($categoryIcon); ?>"></i></div>
                            <h4><?php echo e($categoryName); ?></h4>
                            <p><?php echo e('Explore ' . strtolower($categoryName) . ' vehicles curated in the showroom.'); ?></p>
                            <div class="badge-status badge-confirmed"><?php echo number_format($categoryCount); ?> vehicles</div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="glass-card text-center" style="grid-column: 1 / -1; padding: 40px;">
                        <i class="bi bi-tags-fill" style="font-size: 40px; color: var(--color-accent);"></i>
                        <h4 class="mt-3 mb-2">No categories yet</h4>
                        <p class="text-muted mb-0">Once an admin adds categories, they will appear here for customers to browse.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ============================================
         WHY CHOOSE US
         ============================================ -->
    <section class="section">
        <div class="container">
            <div class="section-title">
                <h2>Why Choose AUTOMATES?</h2>
                <p>Experience premium service and unmatched quality</p>
            </div>
            
            <div class="grid grid-3">
                <div class="glass-card">
                    <div class="feature-icon">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <h4>Verified Inventory</h4>
                    <p>All vehicles thoroughly inspected and certified for quality and authenticity</p>
                </div>
                
                <div class="glass-card">
                    <div class="feature-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h4>Transparent Pricing</h4>
                    <p>No hidden charges. Clear pricing with flexible payment options</p>
                </div>
                
                <div class="glass-card">
                    <div class="feature-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <h4>Expert Team</h4>
                    <p>15+ years of experience providing personalized service</p>
                </div>
                
                <div class="glass-card">
                    <div class="feature-icon">
                        <i class="bi bi-truck"></i>
                    </div>
                    <h4>Free Delivery</h4>
                    <p>Complimentary delivery and setup for all purchases</p>
                </div>
                
                <div class="glass-card">
                    <div class="feature-icon">
                        <i class="bi bi-tools"></i>
                    </div>
                    <h4>Warranty Support</h4>
                    <p>Comprehensive warranty and after-sales support included</p>
                </div>
                
                <div class="glass-card">
                    <div class="feature-icon">
                        <i class="bi bi-telephone-fill"></i>
                    </div>
                    <h4>24/7 Support</h4>
                    <p>Round-the-clock customer support for all your needs</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================
         TESTIMONIALS
         ============================================ -->
    <section class="section" style="background: linear-gradient(135deg, rgba(0, 212, 255, 0.05) 0%, rgba(26, 58, 82, 0.05) 100%);">
        <div class="container">
            <div class="section-title">
                <h2>What Our Customers Say</h2>
                <p>Read testimonials from our satisfied clients</p>
            </div>
            
            <div class="grid grid-3">
                <div class="testimonial-card">
                    <div class="testimonial-stars">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p>"Amazing experience! The team was professional and helpful. Got the perfect car at a great price."</p>
                    <strong>John Anderson</strong>
                    <div class="text-muted">Verified Purchase</div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-stars">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p>"AUTOMATES offers the best selection of luxury vehicles. Highly recommended for anyone looking for quality!"</p>
                    <strong>Sarah Mitchell</strong>
                    <div class="text-muted">Verified Purchase</div>
                </div>
                
                <div class="testimonial-card">
                    <div class="testimonial-stars">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <p>"Best dealership experience ever. Quick paperwork, fair prices, and excellent after-sales service."</p>
                    <strong>Michael Chen</strong>
                    <div class="text-muted">Verified Purchase</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================
         CTA SECTION
         ============================================ -->
    <section class="section" style="background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);">
        <div class="container text-center">
            <h2>Ready to Find Your Dream Car?</h2>
            <p style="font-size: 18px; margin-bottom: 32px;">Schedule a test drive today with our expert team</p>
            <div class="flex justify-center gap-2">
                <a href="<?php echo base_url('/register'); ?>" class="btn btn-accent btn-lg">
                    <i class="bi bi-pencil-square"></i> Register Now
                </a>
                <a href="#contact" class="btn btn-ghost btn-lg">
                    <i class="bi bi-telephone-fill"></i> Contact Us
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================
         FOOTER
         ============================================ -->
    <footer style="background: var(--bg-darker); border-top: 1px solid var(--border-color); padding: 60px 0 20px; margin-top: 80px;">
        <div class="container">
            <div class="grid grid-4 mb-4">
                <div>
                    <h5 style="margin-bottom: 20px;"><?php echo e($systemName); ?></h5>
                    <p class="text-muted"><?php echo e((string)($siteSettings['homepage_about'] ?? 'Premium automobile management and dealership platform.')); ?></p>
                    <div class="text-muted" style="font-size: 14px; line-height: 1.7;">
                        <div><i class="bi bi-envelope"></i> <?php echo e((string)($siteSettings['contact_email'] ?? '')); ?></div>
                        <div><i class="bi bi-telephone"></i> <?php echo e((string)($siteSettings['contact_phone'] ?? '')); ?></div>
                        <div><i class="bi bi-geo-alt"></i> <?php echo e((string)($siteSettings['contact_address'] ?? '')); ?></div>
                    </div>
                </div>
                
                <div>
                    <h5 style="margin-bottom: 20px;">Quick Links</h5>
                    <ul style="list-style: none; padding: 0;">
                        <li><a href="<?php echo base_url('/'); ?>" class="text-muted" style="text-decoration: none;">Home</a></li>
                        <li><a href="<?php echo base_url('/vehicles'); ?>" class="text-muted" style="text-decoration: none;">Vehicles</a></li>
                        <li><a href="#about" class="text-muted" style="text-decoration: none;">About</a></li>
                        <li><a href="#contact" class="text-muted" style="text-decoration: none;">Contact</a></li>
                    </ul>
                </div>
                
                <div>
                    <h5 style="margin-bottom: 20px;">Support</h5>
                    <ul style="list-style: none; padding: 0;">
                        <li><a href="#" class="text-muted" style="text-decoration: none;">Help Center</a></li>
                        <li><a href="#" class="text-muted" style="text-decoration: none;">FAQs</a></li>
                        <li><a href="#" class="text-muted" style="text-decoration: none;">Terms</a></li>
                        <li><a href="#" class="text-muted" style="text-decoration: none;">Privacy</a></li>
                    </ul>
                </div>
                
                <div>
                    <h5 style="margin-bottom: 20px;">Connect</h5>
                    <div style="display: flex; gap: 12px;">
                        <a href="#" class="action-btn" title="Facebook"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="action-btn" title="Twitter"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="action-btn" title="Instagram"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="action-btn" title="LinkedIn"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
            </div>
            
            <div style="border-top: 1px solid var(--border-color); padding-top: 20px; text-align: center; color: var(--text-muted);">
                <p>&copy; 2026 <?php echo e($systemName); ?>. <?php echo e((string)($siteSettings['footer_text'] ?? 'All rights reserved.')); ?></p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Theme Toggle Script -->
    <script>
        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        const isAuthenticated = <?php echo json_encode($isAuthenticated); ?>;
        const loginUrl = <?php echo json_encode(base_url('/login')); ?>;
        const csrfToken = <?php echo json_encode(\App\lib\CSRF::token()); ?>;
        
        // Check for saved theme preference or default to dark
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
        
        // Wishlist functionality
        document.querySelectorAll('.wishlist-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                if (!isAuthenticated) {
                    window.location.href = loginUrl;
                    return;
                }
                const vehicleId = btn.dataset.vehicleId;
                try {
                    const response = await fetch('<?php echo base_url('/api/wishlist/toggle'); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ vehicle_id: vehicleId, csrf_token: csrfToken })
                    });
                    const data = await response.json();
                    btn.classList.toggle('active');
                    btn.style.background = data.added ? 'var(--color-danger)' : 'rgba(255, 255, 255, 0.1)';
                } catch (error) {
                    console.error('Error:', error);
                }
            });
        });
        
        // Compare functionality
        document.querySelectorAll('.compare-btn').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                if (!isAuthenticated) {
                    window.location.href = loginUrl;
                    return;
                }
                const vehicleId = btn.dataset.vehicleId;
                try {
                    const response = await fetch('<?php echo base_url('/api/compare/toggle'); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ vehicle_id: vehicleId, csrf_token: csrfToken })
                    });
                    const data = await response.json();
                    btn.classList.toggle('active');
                } catch (error) {
                    console.error('Error:', error);
                }
            });
        });
    </script>
</body>
</html>


<?php
$cfg = config();
$user = $user ?? null;


$themeCookie = $_COOKIE['theme'] ?? 'dark';
$darkMode = ($themeCookie === 'light' || $themeCookie === 'light-mode') ? 'light' : 'dark';

?>

<!doctype html>
<html lang="en" data-theme="<?= e($darkMode) ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= e($pageTitle ?? $cfg['app']['app_name']) ?></title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= base_url('assets/css/theme.css') ?>" rel="stylesheet">
</head>
<body class="app-body">
  <header class="topbar">
    <div class="container">
      <nav class="navbar navbar-expand-lg navbar-dark px-0">
        <a class="navbar-brand" href="<?= base_url('/') ?>">
          <span class="brand-mark">A</span>
          Automates
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="nav">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link" href="<?= base_url('/') ?>">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('/wishlist') ?>">Wishlist</a></li>
            <?php if ($user): ?>
              <li class="nav-item"><a class="nav-link" href="<?= base_url('/dashboard') ?>">Dashboard</a></li>
            <?php endif; ?>
            <?php if ($user && ($user->role ?? '') === 'admin'): ?>
              <li class="nav-item"><a class="nav-link" href="<?= base_url('/admin') ?>">Admin</a></li>
            <?php endif; ?>
          </ul>

          <div class="d-flex align-items-center gap-2 ms-lg-3">
            <button class="btn btn-ghost" id="themeToggle" type="button">Theme</button>

            <?php if ($user): ?>
              <form method="post" action="<?= base_url('/logout') ?>" class="d-none" id="logoutForm"></form>
              <form method="post" action="<?= base_url('/logout') ?>" class="mb-0">
                <?php csrf_field(); ?>
                <button class="btn btn-outline-light btn-sm" type="submit">Logout</button>
              </form>
            <?php else: ?>
              <a class="btn btn-outline-light btn-sm" href="<?= base_url('/login') ?>">Login</a>
            <?php endif; ?>
          </div>
        </div>
      </nav>
    </div>
  </header>

  <main class="py-4">
    <?php // Content
    ?>
    <section class="container">
      <!-- Placeholder content for layout; actual pages will be implemented next. -->
      <div class="glass-card p-4">
        <h1 class="h3 mb-2">Luxury Automobile Management System</h1>
        <p class="text-muted mb-0">Home page and modules will be implemented in subsequent steps.</p>
      </div>
    </section>
  </main>

  <footer class="footer mt-5">
    <div class="container py-4">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-2">
        <div>
          <div class="fw-semibold">Automates</div>
          <div class="text-muted small">Premium dealership interface • PHP 8 • MySQL</div>
        </div>
        <div class="text-muted small">© <?= date('Y') ?> Automates. All rights reserved.</div>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?= base_url('assets/js/theme.js') ?>"></script>
</body>
</html>


<?php
use function App\lib\e;
?>
<!doctype html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= e($pageTitle ?? 'Login') ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= base_url('assets/css/theme.css') ?>" rel="stylesheet">
</head>
<body class="app-body">

  <div class="auth-wrap">
    <div class="auth-card glass-card">
      <div class="text-center mb-4">
        <div class="brand-splash mx-auto mb-2">A</div>
        <h1 class="h4 mb-1">Admin / Customer Login</h1>
        <p class="text-muted small mb-0">Secure session + CSRF protected form.</p>
      </div>

      <?php if (!empty($registered)): ?>
        <div class="alert alert-success" role="alert">Account created successfully. Please sign in.</div>
      <?php endif; ?>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger" role="alert"><?= e((string)$error) ?></div>
      <?php endif; ?>

      <form method="post" action="<?= base_url('/login') ?>" class="mt-3">
        <input type="hidden" name="csrf_token" value="<?= \App\lib\CSRF::token() ?>">

        <div class="mb-3">
          <label class="form-label">Email</label>
          <input name="email" type="email" class="form-control form-control-lg" required autocomplete="email">
        </div>

        <div class="mb-3">
          <label class="form-label">Password</label>
          <input name="password" type="password" class="form-control form-control-lg" required autocomplete="current-password">
        </div>

        <button class="btn btn-primary w-100 btn-lg mt-2" type="submit">Sign In</button>

        <div class="text-center mt-3 text-muted small">
          Need an account? <a href="<?= base_url('/register') ?>">Create one here</a>.
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


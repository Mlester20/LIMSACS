<?php
session_start();

require_once __DIR__ . '/app/helpers/flashMessage.php';
require_once __DIR__ . '/database/config/config.php';
?>

<!DOCTYPE html>
<html
  lang="en"
  class="light-style customizer-hide"
  dir="ltr"
  data-theme="theme-default"
  data-public="<?= BASE_URL ?>/public/assets/"
  data-template="vertical-menu-template-free"
>
<head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />
    <title><?php require_once __DIR__ . '/app/helpers/title.php'; ?> | Sign In</title>
    <meta name="description" content="" />
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/public/assets/img/favicon/logo.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/vendor/fonts/boxicons.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/demo.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/vendor/css/pages/page-auth.css" />
    <script src="<?= BASE_URL ?>/public/assets/vendor/js/helpers.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/config.js"></script>
</head>
<body>

    <?php FlashMessage::showFlash(); ?>

    <div class="auth-page">
      <div class="auth-shell">

        <!-- Brand / hero panel -->
        <aside class="auth-hero">
          <div class="auth-hero-pattern"></div>
          <div class="auth-hero-content">
            <div class="auth-hero-heading">
              <span class="auth-hero-kicker">Student Information Management System</span>
              <h2 class="auth-hero-title">Limsacs</h2>
            </div>
            <p class="auth-hero-tagline">
              One record for every learner &mdash; from enrollment to graduation, kept accurate and secure.
            </p>
            <ul class="auth-hero-points">
              <li><i class='bx bx-shield-quarter'></i> Role-based, permissioned access</li>
              <li><i class='bx bx-id-card'></i> Centralized learner records</li>
              <li><i class='bx bxs-graduation'></i> Enrollment through graduation history</li>
            </ul>
          </div>
          <p class="auth-hero-footer">Aurora Central School &middot; Aurora, Isabela</p>
        </aside>

        <!-- Form panel -->
        <main class="auth-form-panel">
          <div class="auth-form-inner">

            <div class="auth-form-brand">
              <img src="<?= BASE_URL ?>/public/assets/img/favicon/logo.png" alt="Limsacs logo" />
            </div>

            <h1 class="auth-title text-center">Welcome back</h1>
            <!-- <hr> -->
            <p class="auth-desc">Sign in to your account to access the Limsacs system.</p>

            <form id="formAuthentication" class="mb-3" action="<?= BASE_URL ?>/app/controllers/Auth.php" method="POST">

              <div class="mb-3">
                <label for="email" class="auth-label">Email Address</label>
                <div class="input-icon-wrap">
                  <span class="input-icon"><i class='bx bx-envelope'></i></span>
                  <input
                    type="text"
                    class="form-control auth-input"
                    id="email"
                    name="email"
                    placeholder="you@school.edu.ph"
                    autofocus
                  />
                </div>
              </div>

              <div class="mb-3">
                <label for="password" class="auth-label">Password</label>
                <div class="input-icon-wrap">
                  <span class="input-icon"><i class='bx bx-lock'></i></span>
                  <input
                    type="password"
                    id="password"
                    class="form-control auth-input"
                    name="password"
                    placeholder="··········"
                  />
                  <span class="input-icon-right toggle-password" onclick="togglePassword()" style="cursor:pointer;">
                    <i class='bx bx-hide' id="toggleIcon"></i>
                  </span>
                </div>
              </div>
              <div class="mb-3">
                <button class="btn auth-btn w-100" type="submit">Sign in</button>
              </div>
            </form>

            <p class="auth-footer-text">Protected access &middot; Aurora Central School</p>
          </div>
        </main>

      </div>
    </div>

    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/popper/popper.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/js/bootstrap.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/js/menu.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/main.js"></script>
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    
    <script>
      function togglePassword() {
        const pw = document.getElementById('password');
        const icon = document.getElementById('toggleIcon');
        if (pw.type === 'password') {
          pw.type = 'text';
          icon.classList.replace('bx-hide', 'bx-show');
        } else {
          pw.type = 'password';
          icon.classList.replace('bx-show', 'bx-hide');
        }
      }
    </script>
</body>
</html>
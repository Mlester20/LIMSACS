<?php
session_start();

require_once __DIR__ . '/app/helpers/flashMessage.php';
require_once __DIR__ . '/app/helpers/csrf.php';
require_once __DIR__ . '/database/config/config.php';
require_once __DIR__ . '/app/models/PasswordResetModel.php';

$token = $_GET['token'] ?? '';

$resetModel = new PasswordResetModel($con);
$tokenRow = $token !== '' ? $resetModel->findValidToken(hash('sha256', $token)) : null;
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
    <title><?php require_once __DIR__ . '/app/helpers/title.php'; ?> | Reset Password</title>
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
              <h2 class="auth-hero-title">LIMSACS</h2>
            </div>
            <p class="auth-hero-tagline">
              Learner's Information Management System At Aurora Central School
            </p>
            <ul class="auth-hero-points">
              <li><i class='bx bx-shield-quarter'></i> Role-based, permissioned access</li>
              <li><i class='bx bx-id-card'></i> Centralized learner records</li>
              <li><i class='bx bxs-graduation'></i> Enrollment through graduation history</li>
            </ul>
          </div>
          <p class="auth-hero-footer"><?= date('Y'); ?> Aurora Central School &middot; Aurora, Isabela</p>
        </aside>

        <!-- Form panel -->
        <main class="auth-form-panel">
          <div class="auth-form-inner">

            <div class="auth-form-brand">
              <img src="<?= BASE_URL ?>/public/assets/img/favicon/logo.png" alt="Limsacs logo" />
            </div>

            <?php if($tokenRow): ?>

              <h1 class="auth-title text-center">Set a new password</h1>
              <p class="auth-desc">Choose a new password for <strong><?= htmlspecialchars($tokenRow['email']) ?></strong>.</p>

              <form id="formResetPassword" class="mb-3" action="<?= BASE_URL ?>/app/controllers/PasswordResetController.php" method="POST">
                <?php echo Csrf::field(); ?>
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                <div class="mb-3">
                  <label for="password" class="auth-label">New Password</label>
                  <div class="input-icon-wrap">
                    <span class="input-icon"><i class='bx bx-lock'></i></span>
                    <input
                      type="password"
                      id="password"
                      class="form-control auth-input"
                      name="password"
                      placeholder="At least 8 characters"
                      minlength="8"
                      required
                    />
                    <span class="input-icon-right toggle-password" onclick="togglePasswordField('password', 'toggleIcon1')" style="cursor:pointer;">
                      <i class='bx bx-hide' id="toggleIcon1"></i>
                    </span>
                  </div>
                </div>

                <div class="mb-3">
                  <label for="confirm_password" class="auth-label">Confirm New Password</label>
                  <div class="input-icon-wrap">
                    <span class="input-icon"><i class='bx bx-lock'></i></span>
                    <input
                      type="password"
                      id="confirm_password"
                      class="form-control auth-input"
                      name="confirm_password"
                      placeholder="Re-enter your new password"
                      minlength="8"
                      required
                    />
                    <span class="input-icon-right toggle-password" onclick="togglePasswordField('confirm_password', 'toggleIcon2')" style="cursor:pointer;">
                      <i class='bx bx-hide' id="toggleIcon2"></i>
                    </span>
                  </div>
                </div>

                <div class="mb-3">
                  <button class="btn auth-btn w-100" type="submit" name="confirm_reset">Reset Password</button>
                </div>
              </form>

            <?php else: ?>

              <h1 class="auth-title text-center">Link invalid or expired</h1>
              <p class="auth-desc">This password reset link is no longer valid. Please request a new one.</p>
              <div class="mb-3">
                <a href="<?= BASE_URL ?>/forgot-password.php" class="btn auth-btn w-100">Request a new link</a>
              </div>

            <?php endif; ?>

            <p class="auth-footer-text"><a href="<?= BASE_URL ?>/index.php">Back to sign in</a></p>
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
      function togglePasswordField(fieldId, iconId) {
        const pw = document.getElementById(fieldId);
        const icon = document.getElementById(iconId);
        if (pw.type === 'password') {
          pw.type = 'text';
          icon.classList.replace('bx-hide', 'bx-show');
        } else {
          pw.type = 'password';
          icon.classList.replace('bx-show', 'bx-hide');
        }
      }

      <?php if($tokenRow): ?>
      document.getElementById('formResetPassword').addEventListener('submit', function (e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        if (password !== confirmPassword) {
          e.preventDefault();
          alert('Passwords do not match.');
        }
      });
      <?php endif; ?>
    </script>
</body>
</html>

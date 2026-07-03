<?php
session_start();
require_once __DIR__ . '/../../../app/middleware/auth.php';
AuthRole::allowOnly(['teacher']);
require_once __DIR__ . '/../../../app/controllers/teacher/DashboardController.php';

$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$int = fn($v) => number_format((int) $v);

$sections    = $data['sections'] ?? [];
$gradeLevels = $data['grade_levels'] ?? [];
$sectionNames = array_column($sections, 'section_name');
?>

<!DOCTYPE html>
<html
  lang="en"
  class="light-style layout-menu-fixed"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="<?= BASE_URL ?>/public/assets/"
  data-template="vertical-menu-template-free"
>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Dashboard | <?php require_once __DIR__ . '/../../../app/helpers/title.php'; ?> </title>
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
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/vendor/libs/apex-charts/apex-charts.css" />
    <script src="<?= BASE_URL ?>/public/assets/vendor/js/helpers.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/config.js"></script>
</head>
<body>

    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
    <?php require_once __DIR__ . '/partials/topbar.php'; ?>

    <?php if (!empty($data['error'])): ?>
      <div class="alert alert-danger d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="bx bx-error-circle fs-5"></i>
        <span><?= $e($data['error']) ?></span>
      </div>
    <?php endif; ?>

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
      <div>
        <h4 class="fw-bold mb-0">Teacher Dashboard</h4>
        <small class="text-muted"><?= date('l, F j, Y') ?></small>
      </div>
    </div>

    <div class="row g-4 mb-4">

      <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="avatar avatar-lg flex-shrink-0">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="bx bx-calendar-check bx-sm"></i>
              </span>
            </div>
            <div>
              <p class="text-muted small mb-0">Active School Year</p>
              <h3 class="mb-0 fw-bold"><?= $e($data['active_school_year'] ?? '—') ?></h3>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="avatar avatar-lg flex-shrink-0">
              <span class="avatar-initial rounded bg-label-success">
                <i class="bx bx-group bx-sm"></i>
              </span>
            </div>
            <div>
              <p class="text-muted small mb-0">Total Students</p>
              <h3 class="mb-0 fw-bold"><?= $int($data['total_students'] ?? 0) ?></h3>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="avatar avatar-lg flex-shrink-0">
              <span class="avatar-initial rounded bg-label-warning">
                <i class="bx bx-grid-alt bx-sm"></i>
              </span>
            </div>
            <div>
              <p class="text-muted small mb-0">Section</p>
              <h3 class="mb-0 fw-bold"><?= $e($sectionNames ? implode(', ', $sectionNames) : '—') ?></h3>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="avatar avatar-lg flex-shrink-0">
              <span class="avatar-initial rounded bg-label-info">
                <i class="bx bx-layer bx-sm"></i>
              </span>
            </div>
            <div>
              <p class="text-muted small mb-0">Grade Level</p>
              <h3 class="mb-0 fw-bold"><?= $e($gradeLevels ? implode(', ', $gradeLevels) : '—') ?></h3>
            </div>
          </div>
        </div>
      </div>

    </div><!-- /Row 1 -->

    <?php if (!empty($sections)): ?>
      <div class="row g-4 mb-4">
        <div class="col-12">
          <div class="card border-0 shadow-sm">
            <div class="card-header py-3">
              <h6 class="mb-0 fw-bold">My Sections (Active School Year)</h6>
              <small class="text-muted">Enrolled students vs. maximum capacity per section</small>
            </div>
            <div class="card-body">
              <?php foreach ($sections as $sec): ?>
                <?php
                  $max      = (int) $sec['max_students'];
                  $enrolled = (int) $sec['enrolled_count'];
                  $pct      = $max > 0 ? round($enrolled / $max * 100) : 0;
                  $barColor = $pct >= 100 ? 'danger' : ($pct >= 75 ? 'warning' : 'success');
                ?>
                <div class="mb-3">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-semibold">
                      <?= $e($sec['section_name']) ?>
                      <span class="text-muted fw-normal">&mdash; <?= $e($sec['grade_level']) ?></span>
                    </span>
                    <span class="text-muted small"><?= $int($enrolled) ?> / <?= $int($max) ?> (<?= $pct ?>%)</span>
                  </div>
                  <div class="progress" style="height:6px;">
                    <div class="progress-bar bg-<?= $barColor ?>" style="width:<?= min($pct, 100) ?>%"></div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    <?php else: ?>
      <div class="row g-4 mb-4">
        <div class="col-12">
          <div class="card border-0 shadow-sm">
            <div class="card-body text-center text-muted py-4">
              <i class="bx bx-buildings fs-3 d-block mb-1"></i>
              No section assigned for the active school year.
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <?php require_once __DIR__ . '/partials/footer.php'; ?>
    
    <!-- ── Vendor scripts ── -->
    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/popper/popper.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/js/bootstrap.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/node-waves/node-waves.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/js/menu.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/main.js"></script>

    <!-- ── Chart.js ── -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
    <script src="<?= BASE_URL ?>/public/js/admin/dashboard.js"></script>
</body>
</html>
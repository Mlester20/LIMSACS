<?php
require_once __DIR__ . '/../../../app/controllers/registrar/GraduatesController.php';
require_once __DIR__ . '/../../../app/middleware/Auth.php';
AuthRole::allowOnly(['registrar']);

$e   = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$int = fn($v) => number_format((int) $v);

$data = $controller ? $controller->dashboard() : [];

$jsonOpts = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP;

$syLabels = array_column($data['per_school_year'] ?? [], 'school_year');
$syData   = array_map('intval', array_column($data['per_school_year'] ?? [], 'total'));

$glLabels = array_column($data['per_grade_level'] ?? [], 'grade_level');
$glData   = array_map('intval', array_column($data['per_grade_level'] ?? [], 'total'));

$perSection  = $data['per_section'] ?? [];
$maxSection  = $perSection ? max(array_column($perSection, 'total')) : 0;
?>

<!DOCTYPE html>
<html
  lang="en"
  class="light-style layout-menu-fixed"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="../../../public/assets/"
  data-template="vertical-menu-template-free"
>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Graduates | <?php require_once __DIR__ . '/../../../app/helpers/title.php'; ?> </title>
    <link rel="icon" type="image/x-icon" href="../../../public/assets/img/favicon/logo.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="../../../public/assets/vendor/fonts/boxicons.css" />
    <link rel="stylesheet" href="../../../public/assets/vendor/css/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="../../../public/assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="../../../public/assets/css/demo.css" />
    <link rel="stylesheet" href="../../../public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="../../../public/assets/vendor/libs/apex-charts/apex-charts.css" />
    <script src="../../../public/assets/vendor/js/helpers.js"></script>
    <script src="../../../public/assets/js/config.js"></script>
</head>
<body>
    <?php require_once __DIR__ . '/../../../app/helpers/flashMessage.php'; FlashMessage::showFlash(); ?>

    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
    <?php require_once __DIR__ . '/partials/topbar.php'; ?>

    <!-- ── Page header ───────────────────────────────────────────────────── -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
      <div>
        <h4 class="fw-bold mb-0">Graduate Dashboard</h4>
        <small class="text-muted">Centralized overview of all graduated students</small>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <span class="badge bg-label-info px-3 py-2 fs-6">
          <i class="bx bx-medal me-1"></i>
          Previous School Year Graduates: <strong><?= $int($data['previous_sy_graduates'] ?? 0) ?></strong>
        </span>
        <a href="graduates-master-list.php" class="btn btn-primary">
          <i class="bx bx-table me-1"></i> Master List
        </a>
      </div>
    </div>

    <!-- ── Row 1: Summary cards ────────────────────────────────────────── -->
    <div class="row g-4 mb-4">

      <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="avatar avatar-lg flex-shrink-0">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="bx bx-medal bx-sm"></i>
              </span>
            </div>
            <div>
              <p class="text-muted small mb-0">Total Graduates</p>
              <h3 class="mb-0 fw-bold"><?= $int($data['total_graduates'] ?? 0) ?></h3>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="avatar avatar-lg flex-shrink-0">
              <span class="avatar-initial rounded bg-label-info">
                <i class="bx bx-male-sign bx-sm"></i>
              </span>
            </div>
            <div>
              <p class="text-muted small mb-0">Male Graduates</p>
              <h3 class="mb-0 fw-bold"><?= $int($data['male_graduates'] ?? 0) ?></h3>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="avatar avatar-lg flex-shrink-0">
              <span class="avatar-initial rounded bg-label-pink">
                <i class="bx bx-female-sign bx-sm"></i>
              </span>
            </div>
            <div>
              <p class="text-muted small mb-0">Female Graduates</p>
              <h3 class="mb-0 fw-bold"><?= $int($data['female_graduates'] ?? 0) ?></h3>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="avatar avatar-lg flex-shrink-0">
              <span class="avatar-initial rounded bg-label-success">
                <i class="bx bx-calendar-check bx-sm"></i>
              </span>
            </div>
            <div>
              <p class="text-muted small mb-0">Current School Year Graduates</p>
              <h3 class="mb-0 fw-bold"><?= $int($data['current_sy_graduates'] ?? 0) ?></h3>
            </div>
          </div>
        </div>
      </div>

    </div><!-- /Row 1 -->

    <!-- ── Row 2: Analytics ─────────────────────────────────────────────── -->
    <div class="row g-4 mb-4">

      <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header py-3">
            <h6 class="mb-0 fw-bold">Graduates Per School Year</h6>
          </div>
          <div class="card-body">
            <?php if (!empty($syLabels)): ?>
              <div style="height: 280px;">
                <canvas id="perSchoolYearChart"></canvas>
              </div>
            <?php else: ?>
              <div class="text-center text-muted py-5">
                <i class="bx bx-chart fs-3 d-block mb-1"></i>
                No graduate data yet
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header py-3">
            <h6 class="mb-0 fw-bold">Graduates Per Grade Level</h6>
          </div>
          <div class="card-body">
            <?php if (!empty($glLabels)): ?>
              <div style="height: 280px;">
                <canvas id="perGradeLevelChart"></canvas>
              </div>
            <?php else: ?>
              <div class="text-center text-muted py-5">
                <i class="bx bx-chart fs-3 d-block mb-1"></i>
                No graduate data yet
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div><!-- /Row 2 -->

    <!-- ── Row 3: Per section breakdown ────────────────────────────────── -->
    <div class="row g-4">
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="card-header py-3">
            <h6 class="mb-0 fw-bold">Graduates Per Section</h6>
          </div>
          <div class="card-body">
            <?php if (!empty($perSection)): ?>
              <?php foreach ($perSection as $sec): ?>
                <?php
                  $total = (int) $sec['total'];
                  $pct   = $maxSection > 0 ? round($total / $maxSection * 100) : 0;
                ?>
                <div class="mb-3">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-semibold">
                      <?= $e($sec['section_name'] ?? 'Unassigned') ?>
                      <span class="text-muted fw-normal">&mdash; <?= $e($sec['grade_level'] ?? '') ?></span>
                    </span>
                    <span class="text-muted small"><?= $int($total) ?> graduates</span>
                  </div>
                  <div class="progress" style="height:6px;">
                    <div class="progress-bar bg-primary" style="width:<?= $pct ?>%"></div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="text-center text-muted py-4">
                <i class="bx bx-buildings fs-3 d-block mb-1"></i>
                No graduate data yet
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div><!-- /Row 3 -->

    <?php require_once __DIR__ . '/partials/footer.php'; ?>

    <!-- ── Vendor scripts ── -->
    <script src="../../../public/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../../../public/assets/vendor/libs/popper/popper.js"></script>
    <script src="../../../public/assets/vendor/js/bootstrap.js"></script>
    <script src="../../../public/assets/vendor/libs/node-waves/node-waves.js"></script>
    <script src="../../../public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../../../public/assets/vendor/js/menu.js"></script>
    <script src="../../../public/assets/js/main.js"></script>

    <!-- ── Chart.js ── -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
      const syLabels = <?= json_encode($syLabels, $jsonOpts) ?>;
      const syData   = <?= json_encode($syData, $jsonOpts) ?>;
      const glLabels = <?= json_encode($glLabels, $jsonOpts) ?>;
      const glData   = <?= json_encode($glData, $jsonOpts) ?>;

      document.addEventListener('DOMContentLoaded', function () {
        const syCtx = document.getElementById('perSchoolYearChart');
        if (syCtx && syLabels.length) {
          new Chart(syCtx, {
            type: 'bar',
            data: {
              labels: syLabels,
              datasets: [{
                label: 'Graduates',
                data: syData,
                backgroundColor: '#696cff',
                borderRadius: 6,
                maxBarThickness: 40,
              }],
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: { legend: { display: false } },
              scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            },
          });
        }

        const glCtx = document.getElementById('perGradeLevelChart');
        if (glCtx && glLabels.length) {
          new Chart(glCtx, {
            type: 'bar',
            data: {
              labels: glLabels,
              datasets: [{
                label: 'Graduates',
                data: glData,
                backgroundColor: '#71dd37',
                borderRadius: 6,
                maxBarThickness: 40,
              }],
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: { legend: { display: false } },
              scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            },
          });
        }
      });
    </script>
</body>
</html>

<?php
session_start();

require_once __DIR__ . '/../../../app/controllers/registrar/DashboardController.php';
require_once __DIR__ . '/../../../app/middleware/Auth.php';
AuthRole::allowOnly(['registrar']);
 
$e   = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$int = fn($v) => number_format((int) $v);
 
$statusBadge = static function (string $status): string {
    return match ($status) {
        'Verified' => 'bg-label-success',
        'Pending'  => 'bg-label-warning',
        'Rejected' => 'bg-label-danger',
        default    => 'bg-label-secondary',
    };
};

$statusColor = static function (string $status): string {
    return match ($status) {
        'Verified' => '#71dd37',
        'Pending'  => '#ffab00',
        'Rejected' => '#ff3e1d',
        default    => '#8592a3',
    };
};

$enrollBadge = static function (string $status): string {
    return match ($status) {
        'Enrolled'    => 'bg-label-success',
        'Transferred' => 'bg-label-info',
        'Graduated'   => 'bg-label-primary',
        'Dropped'     => 'bg-label-danger',
        default       => 'bg-label-secondary',
    };
};

$enrollIcon = static function (string $status): string {
    return match ($status) {
        'Enrolled'    => 'bx-user-check',
        'Transferred' => 'bx-shuffle',
        'Graduated'   => 'bx-medal',
        'Dropped'     => 'bx-user-x',
        default       => 'bx-user',
    };
};

// ── Chart.js data prep ──────────────────────────────────────────────────────
$gradeLevelLabels = array_column($data['grade_level_summary'] ?? [], 'grade_level');
$gradeLevelData   = array_map('intval', array_column($data['grade_level_summary'] ?? [], 'total_students'));

$docStatusLabels = array_column($data['document_status_summary'] ?? [], 'status');
$docStatusData   = array_map('intval', array_column($data['document_status_summary'] ?? [], 'total'));
$docStatusColors = array_map($statusColor, $docStatusLabels);

$trendLabels = array_column($data['registration_trend'] ?? [], 'month');
$trendData   = array_map('intval', array_column($data['registration_trend'] ?? [], 'total'));

$jsonOpts = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP;
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
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />
    <title>Home | <?php  require_once __DIR__ . '/../../../app/helpers/title.php'; ?></title>
    <meta name="description" content="" />
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
    <script src="../../../public/assets/vendor/js/helpers.js"></script>
    <script src="../../../public/assets/js/config.js"></script>
</head>
<body>

    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
    <?php require_once __DIR__ . '/partials/topbar.php'; ?>

    <div class="content-wrapper">
      <div class="container-xxl flex-grow-1 container-p-y">
    
        <?php if (!empty($data['error'])): ?>
          <div class="alert alert-danger d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bx bx-error-circle fs-5"></i>
            <span><?= $e($data['error']) ?></span>
          </div>
        <?php endif; ?>
    
        <!-- ── Page header ───────────────────────────────────────────────────── -->
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
          <div>
            <h4 class="fw-bold mb-0">Registrar Dashboard</h4>
            <small class="text-muted">
              <?= date('l, F j, Y') ?> &mdash; School records at a glance
            </small>
          </div>
          <div class="d-flex gap-2 flex-wrap">
            <span class="badge bg-label-primary px-3 py-2 fs-6">
              <i class="bx bx-calendar-check me-1"></i>
              Active School Year<?= (int)($data['active_school_years'] ?? 0) > 1 ? 's' : '' ?>:
              <strong><?= $int($data['active_school_years'] ?? 0) ?></strong>
            </span>
            <span class="badge bg-label-info px-3 py-2 fs-6">
              <i class="bx bx-medal me-1"></i>
              Total Graduates: <strong><?= $int($data['total_graduates'] ?? 0) ?></strong>
            </span>
          </div>
        </div>
    
        <!-- ── Row 1: Student / Section / Year KPIs ──────────────────────────── -->
        <div class="row g-4 mb-4">
    
          <div class="col-sm-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm">
              <div class="card-body d-flex align-items-center gap-3">
                <div class="avatar avatar-lg flex-shrink-0">
                  <span class="avatar-initial rounded bg-label-primary">
                    <i class="bx bx-group bx-sm"></i>
                  </span>
                </div>
                <div>
                  <p class="text-muted small mb-0">Total Students</p>
                  <h3 class="mb-0 fw-bold"><?= $int($data['total_students'] ?? 0) ?></h3>
                </div>
              </div>
              <div class="card-footer bg-transparent border-top-0 pt-0 pb-3 px-3">
                <small class="text-success">
                  <i class="bx bx-user-check"></i>
                  <?= $int($data['enrolled_students'] ?? 0) ?> currently enrolled
                </small>
              </div>
            </div>
          </div>
    
          <div class="col-sm-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm">
              <div class="card-body d-flex align-items-center gap-3">
                <div class="avatar avatar-lg flex-shrink-0">
                  <span class="avatar-initial rounded bg-label-success">
                    <i class="bx bx-user-check bx-sm"></i>
                  </span>
                </div>
                <div>
                  <p class="text-muted small mb-0">Enrolled (Active Year)</p>
                  <h3 class="mb-0 fw-bold"><?= $int($data['enrolled_students'] ?? 0) ?></h3>
                </div>
              </div>
              <div class="card-footer bg-transparent border-top-0 pt-0 pb-3 px-3">
                <?php
                  $total    = (int)($data['total_students'] ?? 0);
                  $enrolled = (int)($data['enrolled_students'] ?? 0);
                  $pct      = $total > 0 ? round($enrolled / $total * 100) : 0;
                ?>
                <div class="d-flex align-items-center gap-2">
                  <div class="progress flex-grow-1" style="height:5px;">
                    <div class="progress-bar bg-success" style="width:<?= $pct ?>%"></div>
                  </div>
                  <small class="text-muted"><?= $pct ?>%</small>
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
                  <p class="text-muted small mb-0">Total Sections</p>
                  <h3 class="mb-0 fw-bold"><?= $int($data['total_sections'] ?? 0) ?></h3>
                </div>
              </div>
              <div class="card-footer bg-transparent border-top-0 pt-0 pb-3 px-3">
                <small class="text-muted">
                  <i class="bx bx-buildings"></i> Across all grade levels
                </small>
              </div>
            </div>
          </div>
    
          <div class="col-sm-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm">
              <div class="card-body d-flex align-items-center gap-3">
                <div class="avatar avatar-lg flex-shrink-0">
                  <span class="avatar-initial rounded bg-label-info">
                    <i class="bx bx-file bx-sm"></i>
                  </span>
                </div>
                <div>
                  <p class="text-muted small mb-0">Required Document Types</p>
                  <h3 class="mb-0 fw-bold"><?= $int($data['required_documents'] ?? 0) ?></h3>
                </div>
              </div>
              <div class="card-footer bg-transparent border-top-0 pt-0 pb-3 px-3">
                <small class="text-muted">
                  <i class="bx bx-check-double"></i> Active &amp; required only
                </small>
              </div>
            </div>
          </div>
    
        </div><!-- /Row 1 -->

        <!-- ── Row 1.5: Academic outcomes (enrollment status) ──────────────────── -->
        <div class="row g-4 mb-4">

          <?php
            $statusOrder  = ['Enrolled', 'Transferred', 'Graduated', 'Dropped'];
            $statusCounts = array_column($data['enrollment_status_summary'] ?? [], 'total', 'status');
          ?>

          <?php foreach ($statusOrder as $status): ?>
            <?php $count = (int) ($statusCounts[$status] ?? 0); ?>
            <div class="col-sm-6 col-xl-3">
              <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                  <div class="avatar avatar-lg flex-shrink-0">
                    <span class="avatar-initial rounded <?= $enrollBadge($status) ?>">
                      <i class="bx <?= $enrollIcon($status) ?> bx-sm"></i>
                    </span>
                  </div>
                  <div>
                    <p class="text-muted small mb-0"><?= $e($status) ?></p>
                    <h3 class="mb-0 fw-bold"><?= $int($count) ?></h3>
                  </div>
                </div>
                <?php if ($status === 'Graduated'): ?>
                  <div class="card-footer bg-transparent border-top-0 pt-0 pb-3 px-3">
                    <small class="text-muted">
                      <i class="bx bx-medal"></i>
                      <?= $int($data['graduates_active_year'] ?? 0) ?> graduated this school year
                    </small>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>

        </div><!-- /Row 1.5 -->

        <!-- ── Row 2: Document status pills ──────────────────────────────────── -->
        <div class="row g-4 mb-4">
    
          <?php
            $docMetrics = [
              [
                'key'   => 'pending_documents',
                'label' => 'Pending',
                'icon'  => 'bx-time-five',
                'color' => 'warning',
                'desc'  => 'Awaiting review',
              ],
              [
                'key'   => 'verified_documents',
                'label' => 'Verified',
                'icon'  => 'bx-check-shield',
                'color' => 'success',
                'desc'  => 'Accepted & complete',
              ],
              [
                'key'   => 'rejected_documents',
                'label' => 'Rejected',
                'icon'  => 'bx-x-circle',
                'color' => 'danger',
                'desc'  => 'Requires resubmission',
              ],
            ];
    
            $totalDocs = (int)($data['pending_documents'] ?? 0)
                      + (int)($data['verified_documents'] ?? 0)
                      + (int)($data['rejected_documents'] ?? 0);
          ?>
    
          <?php foreach ($docMetrics as $m): ?>
            <?php
              $count = (int)($data[$m['key']] ?? 0);
              $share = $totalDocs > 0 ? round($count / $totalDocs * 100) : 0;
            ?>
            <div class="col-sm-6 col-xl-4">
              <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                  <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="badge bg-label-<?= $m['color'] ?> p-2 rounded fs-6">
                      <i class="bx <?= $m['icon'] ?>"></i>
                    </span>
                    <span class="text-muted small"><?= $share ?>% of submissions</span>
                  </div>
                  <h3 class="mb-1 fw-bold"><?= $int($count) ?></h3>
                  <p class="mb-0 fw-semibold"><?= $m['label'] ?> Documents</p>
                  <small class="text-muted"><?= $m['desc'] ?></small>
                  <div class="progress mt-3" style="height:4px;">
                    <div class="progress-bar bg-<?= $m['color'] ?>" style="width:<?= $share ?>%"></div>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
    
        </div><!-- /Row 2 -->
    
        <!-- ── Row 3: Recent tables ───────────────────────────────────────────── -->
        <div class="row g-4 mb-4">
    
          <!-- Recent Student Registrations -->
          <div class="col-12 col-xl-7">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-header d-flex align-items-center justify-content-between py-3">
                <div>
                  <h6 class="mb-0 fw-bold">Recent Registrations</h6>
                  <small class="text-muted">Latest 10 student records</small>
                </div>
                <span class="badge bg-primary rounded-pill">10</span>
              </div>
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th class="ps-3">LRN</th>
                      <th>Full Name</th>
                      <th>Gender</th>
                      <th>Contact</th>
                 
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (!empty($data['recent_registrations'])): ?>
                      <?php foreach ($data['recent_registrations'] as $s): ?>
                        <tr>
                          <td class="ps-3">
                            <code class="text-primary"><?= $e($s['lrn']) ?></code>
                          </td>
                          <td class="fw-semibold"><?= $e($s['full_name']) ?></td>
                          <td>
                            <?php $g = strtolower($s['gender'] ?? ''); ?>
                            <span class="badge bg-label-<?= $g === 'male' ? 'info' : 'pink' ?>">
                              <?= $e($s['gender']) ?>
                            </span>
                          </td>
                          <td class="text-muted"><?= $e($s['contact_number'] ?: '—') ?></td>
             
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                          <i class="bx bx-user-x fs-3 d-block mb-1"></i>
                          No registrations yet
                        </td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
    
          <!-- Grade Level Breakdown -->
          <div class="col-12 col-xl-5">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-header py-3">
                <h6 class="mb-0 fw-bold">Enrollment by Grade Level</h6>
                <small class="text-muted">All academic history records</small>
              </div>
              <div class="card-body">
                <?php if (!empty($data['grade_level_summary'])): ?>
                  <div style="height: 280px;">
                    <canvas id="gradeLevelChart"></canvas>
                  </div>
                <?php else: ?>
                  <div class="text-center text-muted py-5">
                    <i class="bx bx-chart fs-3 d-block mb-1"></i>
                    No enrollment data
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

        </div><!-- /Row 3 -->

        <!-- ── Row 3.5: Section capacity ───────────────────────────────────────── -->
        <div class="row g-4 mb-4">
          <div class="col-12">
            <div class="card border-0 shadow-sm">
              <div class="card-header py-3">
                <h6 class="mb-0 fw-bold">Section Capacity (Active School Year)</h6>
                <small class="text-muted">Enrolled students vs. maximum capacity per section</small>
              </div>
              <div class="card-body">
                <?php if (!empty($data['section_capacity'])): ?>
                  <?php foreach ($data['section_capacity'] as $sec): ?>
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
                <?php else: ?>
                  <div class="text-center text-muted py-4">
                    <i class="bx bx-buildings fs-3 d-block mb-1"></i>
                    No active sections found
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div><!-- /Row 3.5 -->
    
        <!-- ── Row 4: Document uploads + status summary ───────────────────────── -->
        <div class="row g-4">
    
          <!-- Recent Document Uploads -->
          <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-header d-flex align-items-center justify-content-between py-3">
                <div>
                  <h6 class="mb-0 fw-bold">Recent Document Uploads</h6>
                  <small class="text-muted">Latest 10 submissions</small>
                </div>
                <span class="badge bg-primary rounded-pill">10</span>
              </div>
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th class="ps-3">Student</th>
                      <th>Document</th>
                      <th>Status</th>
                      <th class="pe-3">Uploaded</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (!empty($data['recent_uploads'])): ?>
                      <?php foreach ($data['recent_uploads'] as $doc): ?>
                        <tr>
                          <td class="ps-3 fw-semibold"><?= $e($doc['student_name']) ?></td>
                          <td class="text-muted"><?= $e($doc['document_name']) ?></td>
                          <td>
                            <span class="badge <?= $statusBadge($doc['status']) ?>">
                              <?= $e($doc['status']) ?>
                            </span>
                          </td>
                          <td class="pe-3 text-muted small">
                            <?= $e(date('M j, Y g:i A', strtotime($doc['uploaded_at']))) ?>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                          <i class="bx bx-folder-open fs-3 d-block mb-1"></i>
                          No uploads yet
                        </td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
    
          <!-- Document Status Summary -->
          <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-header py-3">
                <h6 class="mb-0 fw-bold">Submission Breakdown</h6>
                <small class="text-muted">All document records</small>
              </div>
              <div class="card-body">
                <?php if (!empty($data['document_status_summary'])): ?>
                  <?php $sumTotal = array_sum(array_column($data['document_status_summary'], 'total')); ?>
                  <div style="height: 220px;">
                    <canvas id="docStatusChart"></canvas>
                  </div>
                  <hr class="my-3">
                  <div class="d-flex justify-content-between">
                    <span class="text-muted small">Total Submissions</span>
                    <strong><?= $int($sumTotal) ?></strong>
                  </div>
                <?php else: ?>
                  <div class="text-center text-muted py-4">
                    <i class="bx bx-pie-chart-alt fs-3 d-block mb-1"></i>
                    No data available
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

        </div><!-- /Row 4 -->

        <!-- ── Row 5: Registration trend ───────────────────────────────────────── -->
        <div class="row g-4 mt-4">
          <div class="col-12">
            <div class="card border-0 shadow-sm">
              <div class="card-header py-3">
                <h6 class="mb-0 fw-bold">Student Registration Trend</h6>
                <small class="text-muted">New student records over the last 6 months</small>
              </div>
              <div class="card-body">
                <?php if (!empty($data['registration_trend'])): ?>
                  <div style="height: 300px;">
                    <canvas id="registrationTrendChart"></canvas>
                  </div>
                <?php else: ?>
                  <div class="text-center text-muted py-5">
                    <i class="bx bx-trending-up fs-3 d-block mb-1"></i>
                    No registration data
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div><!-- /Row 5 -->

      </div><!-- /container-xxl -->
    </div><!-- /content-wrapper -->

    <?php require_once __DIR__ . '/partials/footer.php'; ?>


    <script src="../../../public/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../../../public/assets/vendor/libs/popper/popper.js"></script>
    <script src="../../../public/assets/vendor/js/bootstrap.js"></script>
    <script src="../../../public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../../../public/assets/vendor/js/menu.js"></script>
    <script src="../../../public/assets/js/main.js"></script>
    <script async defer src="https://buttons.github.io/buttons.js"></script>

    <!-- Chart.js (dashboard graphs) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
      const gradeLevelLabels = <?= json_encode($gradeLevelLabels, $jsonOpts) ?>;
      const gradeLevelData   = <?= json_encode($gradeLevelData, $jsonOpts) ?>;

      const docStatusLabels = <?= json_encode($docStatusLabels, $jsonOpts) ?>;
      const docStatusData   = <?= json_encode($docStatusData, $jsonOpts) ?>;
      const docStatusColors = <?= json_encode($docStatusColors, $jsonOpts) ?>;

      const trendLabels = <?= json_encode($trendLabels, $jsonOpts) ?>;
      const trendData    = <?= json_encode($trendData, $jsonOpts) ?>;

      document.addEventListener('DOMContentLoaded', function () {
        const gradeLevelCtx = document.getElementById('gradeLevelChart');
        if (gradeLevelCtx && gradeLevelLabels.length) {
          new Chart(gradeLevelCtx, {
            type: 'bar',
            data: {
              labels: gradeLevelLabels,
              datasets: [{
                label: 'Students',
                data: gradeLevelData,
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

        const docStatusCtx = document.getElementById('docStatusChart');
        if (docStatusCtx && docStatusLabels.length) {
          new Chart(docStatusCtx, {
            type: 'doughnut',
            data: {
              labels: docStatusLabels,
              datasets: [{
                data: docStatusData,
                backgroundColor: docStatusColors,
                borderWidth: 0,
              }],
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: { legend: { position: 'bottom' } },
            },
          });
        }

        const trendCtx = document.getElementById('registrationTrendChart');
        if (trendCtx && trendLabels.length) {
          new Chart(trendCtx, {
            type: 'line',
            data: {
              labels: trendLabels,
              datasets: [{
                label: 'New Registrations',
                data: trendData,
                borderColor: '#696cff',
                backgroundColor: 'rgba(105, 108, 255, 0.15)',
                fill: true,
                tension: 0.35,
                pointRadius: 4,
                pointBackgroundColor: '#696cff',
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
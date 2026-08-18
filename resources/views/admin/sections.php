<?php
require_once __DIR__ . '/../../../app/controllers/admin/SectionsController.php';
require_once __DIR__ . '/../../../app/middleware/auth.php';
AuthRole::allowOnly(['admin']);
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
    <title> Sections | <?php require_once __DIR__ . '/../../../app/helpers/title.php'; ?> </title>
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

    <!-- view section modal (read-only) -->
    <div class="modal fade" id="viewSectionModal" tabindex="-1" aria-labelledby="viewSectionModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content border-0 rounded-3 overflow-hidden">

          <div class="modal-header border-bottom px-4 py-3">
            <div class="d-flex align-items-center gap-2">
              <div class="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 rounded-2" style="width:34px;height:34px;">
                <i class="bi bi-grid text-primary fs-5"></i>
              </div>
              <h5 class="modal-title mb-0 fw-medium" id="viewSectionModalLabel">Section details</h5>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body px-4 py-3">

            <div class="row g-3 mb-3">
              <div class="col-6">
                <div class="bg-light rounded-2 p-3">
                  <p class="text-uppercase text-muted mb-1" style="font-size:11px;letter-spacing:.05em;">Section name</p>
                  <p class="mb-0 fw-medium fs-6" id="view_section_name">-</p>
                </div>
              </div>
              <div class="col-6">
                <div class="bg-light rounded-2 p-3">
                  <p class="text-uppercase text-muted mb-1" style="font-size:11px;letter-spacing:.05em;">Grade level</p>
                  <p class="mb-0 fw-medium fs-6" id="view_section_grade_level">-</p>
                </div>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-6">
                <div class="bg-light rounded-2 p-3">
                  <p class="text-uppercase text-muted mb-1" style="font-size:11px;letter-spacing:.05em;">Adviser</p>
                  <p class="mb-0 fw-medium fs-6" id="view_adviser_name">-</p>
                </div>
              </div>
              <div class="col-6">
                <div class="bg-light rounded-2 p-3">
                  <p class="text-uppercase text-muted mb-1" style="font-size:11px;letter-spacing:.05em;">School year</p>
                  <p class="mb-0 fw-medium fs-6" id="view_school_year">-</p>
                </div>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-6">
                <div class="bg-light rounded-2 p-3">
                  <p class="text-uppercase text-muted mb-1" style="font-size:11px;letter-spacing:.05em;">Enrolled students</p>
                  <p class="mb-0 fw-medium text-primary" style="font-size:2rem;line-height:1;" id="view_total_students">0</p>
                </div>
              </div>
              <div class="col-6">
                <div class="bg-light rounded-2 p-3">
                  <p class="text-uppercase text-muted mb-1" style="font-size:11px;letter-spacing:.05em;">Max capacity</p>
                  <p class="mb-0 fw-medium" style="font-size:2rem;line-height:1;" id="view_max_capacity">35</p>
                </div>
              </div>
            </div>

            <div class="bg-light rounded-2 p-3">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <p class="text-uppercase text-muted mb-0" style="font-size:11px;letter-spacing:.05em;">Capacity status</p>
                <span class="fw-semibold text-success" style="font-size:15px;" id="view_capacity_info">0 / 35</span>
              </div>
              <div class="progress rounded-pill mb-2" style="height:18px;">
                <div id="view_capacity_progress"
                  class="progress-bar bg-success rounded-pill fw-semibold"
                  role="progressbar"
                  style="width:0%; font-size:12px; transition: width 0.4s ease;"
                  aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                  <span id="view_capacity_percent">0%</span>
                </div>
              </div>
              <p class="text-muted mb-0" style="font-size:12px;" id="view_capacity_label">0% full</p>
            </div>

          </div>

          <div class="modal-footer border-top px-4 py-3">
            <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
          </div>

        </div>
      </div>
    </div>

    <div class="card">
        <h5 class="card-header">Sections</h5>
        <div class="table-responsive nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Section Name</th>
                        <th>Grade Level</th>
                        <th>Teacher Assigned</th>
                        <th>School Year</th>
                        <th>Capacity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($sections)): ?>
                        <?php foreach ($sections as $index => $section): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($section['section_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($section['grade_level'] ?? '') ?></td>
                                <td><?= htmlspecialchars($section['adviser_name'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($section['school_year'] ?? '') ?></td>
                                <td>
                                    <span class="badge bg-primary"><?= (int)($section['total_students'] ?? 0) ?></span>
                                    /
                                    <span class="badge bg-secondary"><?= (int)($section['max_students'] ?? 0) ?></span>
                                </td>
                                <td>
                                    <button
                                        class="btn btn-sm btn-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#viewSectionModal"
                                        onclick="viewSection(
                                            <?= (int)$section['id'] ?>,
                                            '<?= addslashes(htmlspecialchars($section['section_name'] ?? '')) ?>',
                                            '<?= addslashes(htmlspecialchars($section['grade_level'] ?? '')) ?>',
                                            '<?= addslashes(htmlspecialchars($section['adviser_name'] ?? '—')) ?>',
                                            '<?= addslashes(htmlspecialchars($section['school_year'] ?? '')) ?>',
                                            <?= (int)($section['total_students'] ?? 0) ?>,
                                            <?= (int)($section['max_students'] ?? 0) ?>
                                        )"
                                    >
                                        View
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No sections found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php require_once __DIR__ . '/partials/footer.php'; ?>

    <!-- ── Vendor scripts ── -->
    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/popper/popper.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/js/bootstrap.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/node-waves/node-waves.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/js/menu.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/main.js"></script>
    <script src="<?= BASE_URL ?>/public/js/admin/sections.js"></script>
</body>
</html>

<?php
require_once __DIR__ . '/../../../database/config/config.php';
require_once __DIR__ . '/../../../app/controllers/registrar/EnrollmentController.php';
require_once __DIR__ . '/../../../app/models/SchoolSettingsModel.php';
require_once __DIR__ . '/../../../app/middleware/Auth.php';
require_once __DIR__ . '/../../../app/helpers/flashMessage.php';

AuthRole::allowOnly(['registrar']);

$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

try {
    $controller = new EnrollmentController($con);
    $gradeLevels = $controller->getGradeLevels();
    $allSections = $controller->getAllSections();
    $schoolSettings = (new SchoolSettingsModel($con))->getAll();

    $allowedStatusFilters = ['Enrolled', 'Transferred', 'Dropped', 'Graduated', 'All'];
    $statusFilter = $_GET['status'] ?? 'Enrolled';
    if (!in_array($statusFilter, $allowedStatusFilters, true)) {
        $statusFilter = 'Enrolled';
    }

    $gradeLevelFilter = $_GET['grade_level'] ?? '';
    if (!in_array($gradeLevelFilter, $gradeLevels, true)) {
        $gradeLevelFilter = '';
    }

    $sectionFilter = $_GET['section_id'] ?? '';

    $genderFilter = $_GET['gender'] ?? '';
    if (!in_array($genderFilter, ['Male', 'Female'], true)) {
        $genderFilter = '';
    }

    $print_mode = isset($_GET['print']);

    $sectionGroups = $controller->getEnrolledGroupedBySection($statusFilter, $gradeLevelFilter, $genderFilter, $sectionFilter);
} catch (Exception $e2) {
    error_log($e2->getMessage());
    $gradeLevels = $gradeLevels ?? [];
    $allSections = $allSections ?? [];
    $schoolSettings = $schoolSettings ?? [];
    $statusFilter = $statusFilter ?? 'Enrolled';
    $gradeLevelFilter = $gradeLevelFilter ?? '';
    $sectionFilter = $sectionFilter ?? '';
    $genderFilter = $genderFilter ?? '';
    $print_mode = $print_mode ?? false;
    $sectionGroups = [];
}

$totalStudents = 0;
foreach ($sectionGroups as $group) {
    $totalStudents += count($group['students']);
}

$qs = function () use ($statusFilter, $gradeLevelFilter, $sectionFilter, $genderFilter) {
    return '?' . http_build_query([
        'status' => $statusFilter,
        'grade_level' => $gradeLevelFilter,
        'section_id' => $sectionFilter,
        'gender' => $genderFilter,
    ]);
};
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
    <title> Enrollees Per Section | <?php require_once __DIR__ . '/../../../app/helpers/title.php'; ?> </title>
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
    <script src="<?= BASE_URL ?>/public/assets/vendor/js/helpers.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/config.js"></script>
    <?php if ($print_mode): ?>
    <style>
      .layout-menu, .layout-navbar, .content-footer, .no-print { display: none !important; }
      .layout-page { margin-left: 0 !important; }
      .section-group { page-break-inside: avoid; }
    </style>
    <?php endif; ?>
</head>
<body>
    <?php if (!$print_mode): ?>
        <?php FlashMessage::showFlash(); ?>
        <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
        <?php require_once __DIR__ . '/partials/topbar.php'; ?>
    <?php else: ?>
        <div class="text-center mb-3">
            <img src="<?= BASE_URL ?>/public/assets/img/favicon/logo.png" alt="School Logo" style="width:70px;height:70px;">
            <h5 class="fw-bold mb-0 mt-1">Aurora Central School</h5>
            <p class="text-muted small mb-0">Aurora, Isabela</p>
        </div>
    <?php endif; ?>

    <div class="<?= $print_mode ? '' : 'container-xxl flex-grow-1 container-p-y' ?>">
    <div class="card mt-4">
        <h5 class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            Enrollees &mdash; Per Section
            <?php if (!$print_mode): ?>
            <div class="d-flex gap-2 flex-wrap no-print">
                <button type="submit" form="sectionFilterForm" class="btn btn-sm btn-outline-secondary"><i class="bx bx-filter-alt me-1"></i>Filter</button>
                <a href="<?= $e($qs() . '&print=1') ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bx bx-printer me-1"></i>Print</a>
            </div>
            <?php endif; ?>
        </h5>

        <?php if (!$print_mode): ?>
        <div class="card-body border-bottom no-print">
            <form method="GET" id="sectionFilterForm" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select">
                        <option value="Enrolled" <?= $statusFilter === 'Enrolled' ? 'selected' : '' ?>>Enrolled</option>
                        <option value="Transferred" <?= $statusFilter === 'Transferred' ? 'selected' : '' ?>>Transferred</option>
                        <option value="Dropped" <?= $statusFilter === 'Dropped' ? 'selected' : '' ?>>Dropped</option>
                        <option value="Graduated" <?= $statusFilter === 'Graduated' ? 'selected' : '' ?>>Graduated</option>
                        <option value="All" <?= $statusFilter === 'All' ? 'selected' : '' ?>>All</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Grade Level</label>
                    <select name="grade_level" class="form-select">
                        <option value="">All</option>
                        <?php foreach ($gradeLevels as $gl): ?>
                            <option value="<?= $e($gl) ?>" <?= $gradeLevelFilter === $gl ? 'selected' : '' ?>><?= $e($gl) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Section <span class="text-muted fw-normal">(pick one to print just that section)</span></label>
                    <select name="section_id" class="form-select">
                        <option value="">All Sections</option>
                        <?php foreach ($allSections as $sec): ?>
                            <option value="<?= $e($sec['id']) ?>" <?= (string)$sectionFilter === (string)$sec['id'] ? 'selected' : '' ?>><?= $e($sec['section_name'] . ' (' . $sec['grade_level'] . ')') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">All</option>
                        <option value="Male" <?= $genderFilter === 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= $genderFilter === 'Female' ? 'selected' : '' ?>>Female</option>
                    </select>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <div class="card-body">
            <?php if (empty($sectionGroups)): ?>
                <p class="text-center text-muted py-5">No records found.</p>
            <?php else: ?>
                <p class="text-muted small"><?= count($sectionGroups) ?> section(s) &mdash; <?= $totalStudents ?> student(s) total</p>
                <?php foreach ($sectionGroups as $group): ?>
                    <div class="section-group mb-4">
                        <div class="d-flex justify-content-between align-items-center bg-light border rounded px-3 py-2 mb-0">
                            <span class="fw-bold">
                                <?= $e($group['grade_level']) ?> &mdash; <?= $e($group['section_name']) ?>
                                <span class="fw-normal text-muted">(SY <?= $e($group['school_year']) ?>)</span>
                            </span>
                            <span class="text-muted small">
                                Adviser: <?= $e($group['adviser_name'] ?: 'N/A') ?> &middot; <?= count($group['students']) ?> student(s)
                            </span>
                        </div>
                        <table class="table table-sm table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th style="width:40px;">#</th>
                                    <th>LRN</th>
                                    <th>Student Name</th>
                                    <th>Gender</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($group['students'] as $i => $st): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><?= $e($st['lrn']) ?></td>
                                        <td><?= $e($st['first_name'] . ' ' . $st['last_name']) ?></td>
                                        <td><?= $e($st['gender']) ?></td>
                                        <td><?= $e($st['enrollment_status']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($print_mode): ?>
            <?php
                $registrarSigName = trim($schoolSettings['registrar_name'] ?? '');
                $principalSigName = trim($schoolSettings['principal_name'] ?? '');
            ?>
            <div class="row mt-5 mb-3 px-3">
                <div class="col-6 text-center">
                    <p class="mb-5 text-start fw-semibold">Prepared by:</p>
                    <p class="mb-0" style="border-top:1px solid #333; display:inline-block; min-width:220px; padding-top:4px; font-weight:600;">
                        <?= $registrarSigName !== '' ? $e($registrarSigName) : '&nbsp;' ?>
                    </p>
                    <p class="text-muted small mb-0 fst-italic">Signature over Printed Name</p>
                    <p class="text-muted small mb-0"><?= $e($schoolSettings['registrar_title'] ?? 'Registrar') ?></p>
                </div>
                <div class="col-6 text-center">
                    <p class="mb-5 text-start fw-semibold">Checked by:</p>
                    <p class="mb-0" style="border-top:1px solid #333; display:inline-block; min-width:220px; padding-top:4px; font-weight:600;">
                        <?= $principalSigName !== '' ? $e($principalSigName) : '&nbsp;' ?>
                    </p>
                    <p class="text-muted small mb-0 fst-italic">Signature over Printed Name</p>
                    <p class="text-muted small mb-0"><?= $e($schoolSettings['principal_title'] ?? 'School Principal') ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    </div>

    <?php if (!$print_mode): ?>
        <?php require_once __DIR__ . '/partials/footer.php'; ?>
    <?php endif; ?>

    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/popper/popper.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/js/bootstrap.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/js/menu.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/main.js"></script>
    <?php if ($print_mode): ?>
    <script>window.onload = function(){ window.print(); };</script>
    <?php endif; ?>
</body>
</html>

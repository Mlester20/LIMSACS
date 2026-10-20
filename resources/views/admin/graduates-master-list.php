<?php
require_once __DIR__ . '/../../../app/controllers/admin/GraduatesController.php';
require_once __DIR__ . '/../../../app/models/SchoolSettingsModel.php';
require_once __DIR__ . '/../../../app/middleware/Auth.php';
AuthRole::allowOnly(['admin']);

$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$schoolSettings = (new SchoolSettingsModel($con))->getAll();

$search_term         = trim($_GET['search'] ?? '');
$school_year_filter   = $_GET['school_year_id'] ?? '';
$section_filter       = $_GET['section_id'] ?? '';
$gender_filter        = $_GET['gender'] ?? '';
$page                 = $_GET['page'] ?? 1;
$print_mode           = isset($_GET['print']);

// This is the Graduates module -- Grade Level and Status aren't real filters
// here (every row is always Grade 6 / Graduated by definition), so neither
// is offered as a filter control or accepted from the query string.
$filters = [
    'search'         => $search_term,
    'school_year_id' => $school_year_filter,
    'section_id'     => $section_filter,
    'gender'         => $gender_filter,
];

$options = $controller ? $controller->filterOptions() : ['school_years' => [], 'sections' => []];

if ($print_mode) {
    // Print shows every matching record, not just one page -- a batch with
    // more than 10 graduates shouldn't get silently cut off.
    $graduates     = $controller ? $controller->allForPrint($filters) : [];
    $current_page  = 1;
    $total_pages   = 1;
    $total_records = count($graduates);
    $limit         = $total_records ?: 1;
} else {
    $listing = $controller ? $controller->masterList($filters, $page) : ['records' => [], 'current_page' => 1, 'total_pages' => 1, 'total_records' => 0, 'limit' => 10];
    $graduates     = $listing['records'];
    $current_page  = $listing['current_page'];
    $total_pages   = $listing['total_pages'];
    $total_records = $listing['total_records'];
    $limit         = $listing['limit'];
}

// "Batch <school year>" label for the print heading -- inferred from the
// records actually shown, not from whether a School Year filter was picked.
$batchLabel = GraduatesController::inferBatchLabel($graduates);

$qs = function ($p) use ($filters) {
    return '?' . http_build_query(array_merge($filters, ['page' => $p]));
};

$exportQs = function ($type) use ($filters) {
    return BASE_URL . '/app/controllers/admin/GraduatesController.php?' . http_build_query(array_merge($filters, ['export' => $type]));
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
    <title> Graduates Master List | <?php require_once __DIR__ . '/../../../app/helpers/title.php'; ?> </title>
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
    </style>
    <?php endif; ?>
</head>
<body>
    <?php if (!$print_mode): ?>
        <?php require_once __DIR__ . '/../../../app/helpers/flashMessage.php'; FlashMessage::showFlash(); ?>
        <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
        <?php require_once __DIR__ . '/partials/topbar.php'; ?>
    <?php else: ?>
        <div class="text-center mb-3">
            <img src="<?= BASE_URL ?>/public/assets/img/favicon/logo.png" alt="School Logo" style="width:70px;height:70px;">
            <h5 class="fw-bold mb-0 mt-1">Aurora Central School</h5>
            <p class="text-muted small mb-0">Aurora, Isabela</p>
        </div>
    <?php endif; ?>

    <div class="card mt-4">
        <h5 class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <?php if ($print_mode): ?>
                Graduates Master List<?= $batchLabel !== '' ? ' &mdash; Batch ' . $e($batchLabel) : '' ?>
            <?php else: ?>
                Graduates &mdash; Master List
            <?php endif; ?>
            <?php if (!$print_mode): ?>
            <div class="d-flex gap-2 flex-wrap no-print">
                <button type="submit" form="graduatesFilterForm" class="btn btn-sm btn-outline-secondary"><i class="bx bx-filter-alt me-1"></i>Filter</button>
                <a href="<?= $e($exportQs('excel')) ?>" class="btn btn-sm btn-outline-success"><i class="bx bx-spreadsheet me-1"></i>Export Excel</a>
                <a href="<?= $e($exportQs('pdf')) ?>" class="btn btn-sm btn-outline-danger"><i class="bx bx-file-pdf me-1"></i>Export PDF</a>
                <a href="graduates-per-section.php" class="btn btn-sm btn-outline-primary"><i class="bx bx-list-ul me-1"></i>Per Section</a>
                <a href="<?= $e($qs($current_page) . '&print=1') ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bx bx-printer me-1"></i>Print</a>
            </div>
            <?php endif; ?>
        </h5>

        <?php if (!$print_mode): ?>
        <div class="card-body border-bottom no-print">
            <form method="GET" id="graduatesFilterForm" class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label small">School Year</label>
                    <select name="school_year_id" class="form-select">
                        <option value="">All</option>
                        <?php foreach ($options['school_years'] as $sy): ?>
                            <option value="<?= $e($sy['id']) ?>" <?= (string)$school_year_filter === (string)$sy['id'] ? 'selected' : '' ?>><?= $e($sy['school_year']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Section</label>
                    <select name="section_id" class="form-select">
                        <option value="">All</option>
                        <?php foreach ($options['sections'] as $sec): ?>
                            <option value="<?= $e($sec['id']) ?>" <?= (string)$section_filter === (string)$sec['id'] ? 'selected' : '' ?>><?= $e($sec['section_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
            <?php if ($search_term !== '' || $school_year_filter !== '' || $section_filter !== '' || $gender_filter !== ''): ?>
                <a href="graduates-master-list.php" class="btn btn-link btn-sm px-0 mt-2">Clear all filters</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="table-responsive nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>LRN</th>
                        <th>Student Name</th>
                        <th>Gender</th>
                        <?php if (!$print_mode): ?>
                            <th>Grade Level</th>
                            <th>Section</th>
                            <th>Adviser</th>
                            <th>School Year</th>
                            <th>Date Graduated</th>
                            <th>Registrar</th>
                            <th>Status</th>
                            <th class="no-print">Action</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($graduates)): ?>
                        <?php $rowNumber = (($current_page - 1) * $limit) + 1; ?>
                        <?php foreach ($graduates as $g): ?>
                            <tr>
                                <td><?= $rowNumber++ ?></td>
                                <td><code class="text-primary"><?= $e($g['lrn']) ?></code></td>
                                <td class="fw-semibold"><?= $e($g['student_full_name']) ?></td>
                                <td><?= $e($g['gender']) ?></td>
                                <?php if (!$print_mode): ?>
                                    <td><?= $e($g['grade_level']) ?></td>
                                    <td><?= $e($g['section_name'] ?? '—') ?></td>
                                    <td><?= $e($g['adviser_name'] ?? '—') ?></td>
                                    <td><?= $e($g['school_year'] ?? '—') ?></td>
                                    <td><?= !empty($g['graduation_date']) ? date('F j, Y', strtotime($g['graduation_date'])) : '—' ?></td>
                                    <td><?= $e($g['registrar_name'] ?? '—') ?></td>
                                    <td>
                                        <?php
                                            $statusBadges = [
                                                'Graduated' => 'bg-label-primary',
                                                'Transferred' => 'bg-label-warning',
                                                'Dropped' => 'bg-label-danger',
                                            ];
                                        ?>
                                        <span class="badge <?= $statusBadges[$g['enrollment_status']] ?? 'bg-label-secondary' ?>">
                                            <?= $e($g['enrollment_status']) ?>
                                        </span>
                                    </td>
                                    <td class="no-print">
                                        <?php if (!empty($g['graduate_id'])): ?>
                                            <a href="graduate-view.php?id=<?= $e($g['graduate_id']) ?>" class="btn btn-sm btn-outline-primary" title="View" aria-label="View"><i class="bx bx-show"></i></a>
                                        <?php else: ?>
                                            <span class="text-muted small">No graduate profile</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= $print_mode ? 4 : 12 ?>" class="text-center">No graduate records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
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

        <?php if ($total_pages > 1 && !$print_mode): ?>
            <div class="card-footer d-flex justify-content-between align-items-center no-print">
                <span>Page <?= $current_page ?> of <?= $total_pages ?> (<?= $total_records ?> total)</span>
                <nav>
                    <ul class="pagination mb-0">
                        <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= $e($qs(max($current_page - 1, 1))) ?>">Previous</a>
                        </li>
                        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                            <li class="page-item <?= $p === $current_page ? 'active' : '' ?>">
                                <a class="page-link" href="<?= $e($qs($p)) ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= $e($qs(min($current_page + 1, $total_pages))) ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!$print_mode): ?>
        <?php require_once __DIR__ . '/partials/footer.php'; ?>
    <?php endif; ?>

    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/popper/popper.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/js/bootstrap.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/node-waves/node-waves.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/js/menu.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/main.js"></script>
    <?php if ($print_mode): ?>
    <script>window.onload = function(){ window.print(); };</script>
    <?php endif; ?>
</body>
</html>

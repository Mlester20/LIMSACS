<?php
require_once __DIR__ . '/../../../app/controllers/registrar/GraduatesController.php';
require_once __DIR__ . '/../../../app/middleware/Auth.php';
AuthRole::allowOnly(['registrar']);

$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$search_term         = trim($_GET['search'] ?? '');
$school_year_filter   = $_GET['school_year_id'] ?? '';
$grade_level_filter   = $_GET['grade_level'] ?? '';
$section_filter       = $_GET['section_id'] ?? '';
$gender_filter        = $_GET['gender'] ?? '';
$status_filter        = $_GET['status'] ?? '';
$page                 = $_GET['page'] ?? 1;
$print_mode           = isset($_GET['print']);

$filters = [
    'search'         => $search_term,
    'school_year_id' => $school_year_filter,
    'grade_level'    => $grade_level_filter,
    'section_id'     => $section_filter,
    'gender'         => $gender_filter,
    'status'         => $status_filter,
];

$listing = $controller ? $controller->masterList($filters, $page) : ['records' => [], 'current_page' => 1, 'total_pages' => 1, 'total_records' => 0, 'limit' => 10];
$options = $controller ? $controller->filterOptions() : ['school_years' => [], 'grade_levels' => [], 'sections' => []];

$graduates     = $listing['records'];
$current_page  = $listing['current_page'];
$total_pages   = $listing['total_pages'];
$total_records = $listing['total_records'];
$limit         = $listing['limit'];

$qs = function ($p) use ($filters) {
    return '?' . http_build_query(array_merge($filters, ['page' => $p]));
};

$exportQs = function ($type) use ($filters) {
    return '../../../app/controllers/registrar/GraduatesController.php?' . http_build_query(array_merge($filters, ['export' => $type]));
};
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
    <title> Graduates Master List | <?php require_once __DIR__ . '/../../../app/helpers/title.php'; ?> </title>
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
    <?php endif; ?>

    <div class="card mt-4">
        <h5 class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            Graduates &mdash; Master List
            <?php if (!$print_mode): ?>
            <div class="d-flex gap-2 flex-wrap no-print">
                <a href="<?= $e($exportQs('excel')) ?>" class="btn btn-sm btn-outline-success"><i class="bx bx-spreadsheet me-1"></i>Export Excel</a>
                <a href="<?= $e($exportQs('pdf')) ?>" class="btn btn-sm btn-outline-danger"><i class="bx bx-file-pdf me-1"></i>Export PDF</a>
                <a href="<?= $e($qs($current_page) . '&print=1') ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bx bx-printer me-1"></i>Print</a>
            </div>
            <?php endif; ?>
        </h5>

        <?php if (!$print_mode): ?>
        <div class="card-body border-bottom no-print">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="LRN or student name" value="<?= $e($search_term) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">School Year</label>
                    <select name="school_year_id" class="form-select">
                        <option value="">All</option>
                        <?php foreach ($options['school_years'] as $sy): ?>
                            <option value="<?= $e($sy['id']) ?>" <?= (string)$school_year_filter === (string)$sy['id'] ? 'selected' : '' ?>><?= $e($sy['school_year']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Grade Level</label>
                    <select name="grade_level" class="form-select">
                        <option value="">All</option>
                        <?php foreach ($options['grade_levels'] as $gl): ?>
                            <option value="<?= $e($gl) ?>" <?= $grade_level_filter === $gl ? 'selected' : '' ?>><?= $e($gl) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Section</label>
                    <select name="section_id" class="form-select">
                        <option value="">All</option>
                        <?php foreach ($options['sections'] as $sec): ?>
                            <option value="<?= $e($sec['id']) ?>" <?= (string)$section_filter === (string)$sec['id'] ? 'selected' : '' ?>><?= $e($sec['section_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label small">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">All</option>
                        <option value="Male" <?= $gender_filter === 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= $gender_filter === 'Female' ? 'selected' : '' ?>>Female</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="Graduated" <?= $status_filter === 'Graduated' ? 'selected' : '' ?>>Graduated</option>
                        <option value="Transferred" <?= $status_filter === 'Transferred' ? 'selected' : '' ?>>Transferred</option>
                        <option value="Dropped" <?= $status_filter === 'Dropped' ? 'selected' : '' ?>>Dropped</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-secondary w-100">Filter</button>
                </div>
            </form>
            <?php if ($search_term !== '' || $school_year_filter !== '' || $grade_level_filter !== '' || $section_filter !== '' || $gender_filter !== '' || $status_filter !== ''): ?>
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
                        <th>Grade Level</th>
                        <th>Section</th>
                        <th>Adviser</th>
                        <th>School Year</th>
                        <th>Date Graduated</th>
                        <th>Registrar</th>
                        <th>Status</th>
                        <?php if (!$print_mode): ?><th class="no-print">Action</th><?php endif; ?>
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
                                <td><?= $e($g['grade_level']) ?></td>
                                <td><?= $e($g['section_name'] ?? '—') ?></td>
                                <td><?= $e($g['adviser_name'] ?? '—') ?></td>
                                <td><?= $e($g['school_year'] ?? '—') ?></td>
                                <td><?= !empty($g['graduation_date']) ? date('F j, Y', strtotime($g['graduation_date'])) : '—' ?></td>
                                <td><?= $e($g['registrar_name'] ?? '—') ?></td>
                                <td>
                                    <span class="badge <?= $g['enrollment_status'] === 'Graduated' ? 'bg-label-primary' : 'bg-label-secondary' ?>">
                                        <?= $e($g['enrollment_status']) ?>
                                    </span>
                                </td>
                                <?php if (!$print_mode): ?>
                                <td class="no-print">
                                    <a href="graduate-view.php?id=<?= $e($g['graduate_id']) ?>" class="btn btn-sm btn-outline-primary">View</a>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="12" class="text-center">No graduate records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

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

    <script src="../../../public/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../../../public/assets/vendor/libs/popper/popper.js"></script>
    <script src="../../../public/assets/vendor/js/bootstrap.js"></script>
    <script src="../../../public/assets/vendor/libs/node-waves/node-waves.js"></script>
    <script src="../../../public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../../../public/assets/vendor/js/menu.js"></script>
    <script src="../../../public/assets/js/main.js"></script>
    <?php if ($print_mode): ?>
    <script>window.onload = function(){ window.print(); };</script>
    <?php endif; ?>
</body>
</html>

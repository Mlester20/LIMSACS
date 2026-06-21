<?php
require_once __DIR__ . '/../../../app/controllers/admin/AcademicHistoryController.php';
require_once __DIR__ . '/../../../app/middleware/auth.php';
AuthRole::allowOnly(['admin']); 
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
    <title> Academic History | <?php require_once __DIR__ . '/../../../app/helpers/title.php'; ?> </title>
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

    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
    <?php require_once __DIR__ . '/partials/topbar.php'; ?>

    <div class="card">
        <h5 class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            Academic History
            <form method="GET" class="d-flex gap-2">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    style="max-width: 220px;"
                    placeholder="Search student name"
                    value="<?php echo htmlspecialchars($search_term); ?>">

                <select name="school_year_id" class="form-select" style="max-width: 180px;">
                    <option value="">All School Years</option>
                    <?php foreach ($school_year_options as $option): ?>
                        <option value="<?php echo htmlspecialchars($option['id']); ?>" <?php echo (string)$school_year_filter === (string)$option['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($option['school_year']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn btn-outline-secondary">Filter</button>
                <?php if ($search_term !== '' || $school_year_filter !== ''): ?>
                    <a href="academic-history.php" class="btn btn-outline-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </h5>
        <div class="table-responsive nowrap">
            <table class="table">
                <tr>
                    <th>#</th>
                    <th>Student Name</th>
                    <th>Grade & Section</th>
                    <th>School Year</th>
                    <th>Enrolled By</th>
                    <th>Enrolled At</th>
                </tr>

                <?php if (!empty($academic_histories)): ?>
                    <?php
                        // Row numbering should continue across pages, not reset to 1
                        $rowNumber = (($current_page - 1) * $limit) + 1;
                    ?>
                    <?php foreach ($academic_histories as $history): ?>
                        <tr>
                            <td><?= $rowNumber++ ?></td>
                            <td><?= htmlspecialchars($history['student_full_name'] ?? '') ?></td>
                            <td>
                                <?= htmlspecialchars($history['section_grade_level'] ?? '') ?>
                                <?= !empty($history['section_name']) ? '- ' . htmlspecialchars($history['section_name']) : '' ?>
                            </td>
                            <td><?= htmlspecialchars($history['school_year'] ?? '') ?></td>
                            <td><?= htmlspecialchars($history['enrolled_by_registrar_name'] ?? '') ?></td>
                            <!-- format into month name and date -->
                            <td><?= !empty($history['created_at']) ? date('F j, Y', strtotime($history['created_at'])) : '' ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No academic history records found.</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <span>
                    Page <?= $current_page ?> of <?= $total_pages ?>
                    (<?= $total_records ?> total record<?= $total_records === 1 ? '' : 's' ?>)
                </span>

                <nav>
                    <?php $qs = function ($p) use ($search_term, $school_year_filter) { return '?' . http_build_query(['search' => $search_term, 'school_year_id' => $school_year_filter, 'page' => $p]); }; ?>
                    <ul class="pagination mb-0">
                        <!-- Previous -->
                        <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= $qs(max($current_page - 1, 1)) ?>">Previous</a>
                        </li>

                        <!-- Numbered pages -->
                        <?php
                            // Show a small window of page numbers around the current page
                            $windowSize = 2;
                            $startPage = max(1, $current_page - $windowSize);
                            $endPage = min($total_pages, $current_page + $windowSize);
                        ?>

                        <?php if ($startPage > 1): ?>
                            <li class="page-item"><a class="page-link" href="<?= $qs(1) ?>">1</a></li>
                            <?php if ($startPage > 2): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($p = $startPage; $p <= $endPage; $p++): ?>
                            <li class="page-item <?= $p === $current_page ? 'active' : '' ?>">
                                <a class="page-link" href="<?= $qs($p) ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($endPage < $total_pages): ?>
                            <?php if ($endPage < $total_pages - 1): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            <li class="page-item"><a class="page-link" href="<?= $qs($total_pages) ?>"><?= $total_pages ?></a></li>
                        <?php endif; ?>

                        <!-- Next -->
                        <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= $qs(min($current_page + 1, $total_pages)) ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>

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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
    <script src="../../../public/js/admin/dashboard.js"></script>
</body>
</html>
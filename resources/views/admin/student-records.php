<?php
require_once __DIR__ . '/../../../app/controllers/admin/StudentsController.php';
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
    <title> Student Master List | <?php require_once __DIR__ . '/../../../app/helpers/title.php'; ?> </title>
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

    <div class="card">
        <h5 class="card-header">Student Master List</h5>
        <div class="table-responsive nowrap">
            <table class="table">
                <tr>
                    <th>#</th>
                    <th>LRN</th>
                    <th>Full Name</th>
                    <th>Gender</th>
                    <th>Birth Date</th>
                    <th>Address</th>
                    <th>Contact Number</th>
                </tr>

                <?php if (!empty($students)): ?>
                    <?php
                        // Row numbering should continue across pages, not reset to 1
                        $rowNumber = (($current_page - 1) * $limit) + 1;
                    ?>
                    <?php foreach ($students as $student): ?>
                        <?php
                            $fullName = trim(
                                ($student['first_name'] ?? '') . ' ' .
                                ($student['middle_name'] ?? '') . ' ' .
                                ($student['last_name'] ?? '') . ' ' .
                                ($student['suffix'] ?? '')
                            );
                            $fullName = preg_replace('/\s+/', ' ', $fullName);
                        ?>
                        <tr>
                            <td><?= $rowNumber++ ?></td>
                            <td><?= htmlspecialchars($student['lrn'] ?? '') ?></td>
                            <td><?= htmlspecialchars($fullName) ?></td>
                            <td><?= htmlspecialchars($student['gender'] ?? '') ?></td>
                            <td><?= !empty($student['birth_date']) ? date('F j, Y', strtotime($student['birth_date'])) : '' ?></td>
                            <td><?= htmlspecialchars($student['address'] ?? '') ?></td>
                            <td><?= htmlspecialchars($student['contact_number'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No student records found.</td>
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
                    <?php $qs = function ($p) { return '?' . http_build_query(['page' => $p]); }; ?>
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
    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/popper/popper.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/js/bootstrap.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/node-waves/node-waves.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/js/menu.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/main.js"></script>
</body>
</html>

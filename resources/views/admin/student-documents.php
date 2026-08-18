<?php
require_once __DIR__ . '/../../../app/controllers/admin/StudentsDocumentController.php';
require_once __DIR__ . '/../../../app/middleware/auth.php';
AuthRole::allowOnly(['admin']);

// Status -> [badge class, icon] used for the status badge (matches registrar/student-documents.php).
$statusMeta = function (string $status): array {
    return match ($status) {
        'Verified'  => ['bg-label-success', 'bx-check-circle'],
        'Submitted' => ['bg-label-info', 'bx-upload'],
        'Pending'   => ['bg-label-warning', 'bx-time-five'],
        'Rejected'  => ['bg-label-danger', 'bx-x-circle'],
        default     => ['bg-label-secondary', 'bx-file'],
    };
};

// Normalize the stored relative path into the absolute /storage/... URL used for previews.
$fileUrl = function (?string $path): string {
    if (empty($path)) {
        return '#';
    }
    return '/storage/student_documents/' . basename($path);
};

$status_options = ['Submitted', 'Pending', 'Verified', 'Rejected'];
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
    <title> Student Documents | <?php require_once __DIR__ . '/../../../app/helpers/title.php'; ?> </title>
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
        <h5 class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            Student Documents
            <form method="GET" class="d-flex gap-2">
                <select name="status" class="form-select" style="max-width: 180px;">
                    <option value="">All Statuses</option>
                    <?php foreach ($status_options as $status): ?>
                        <option value="<?php echo htmlspecialchars($status); ?>" <?php echo $status_filter === $status ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($status); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn btn-outline-secondary">Filter</button>
                <?php if ($status_filter !== ''): ?>
                    <a href="student-documents.php" class="btn btn-outline-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </h5>
        <div class="table-responsive nowrap">
            <table class="table">
                <tr>
                    <th>#</th>
                    <th>Student Name</th>
                    <th>Document Type</th>
                    <th>Status</th>
                    <th>Uploaded By</th>
                    <th>Uploaded At</th>
                    <th>File</th>
                </tr>

                <?php if (!empty($documents)): ?>
                    <?php
                        // Row numbering should continue across pages, not reset to 1
                        $rowNumber = (($current_page - 1) * $limit) + 1;
                    ?>
                    <?php foreach ($documents as $document): ?>
                        <?php [$statusBadgeClass, $statusIcon] = $statusMeta($document['status']); ?>
                        <tr>
                            <td><?= $rowNumber++ ?></td>
                            <td><?= htmlspecialchars(trim(($document['student_first_name'] ?? '') . ' ' . ($document['student_last_name'] ?? ''))) ?></td>
                            <td><?= htmlspecialchars($document['document_type_name'] ?? '') ?></td>
                            <td>
                                <span class="badge <?= $statusBadgeClass ?>">
                                    <i class="bx <?= $statusIcon ?> me-1"></i><?= htmlspecialchars($document['status'] ?? '') ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($document['uploaded_by_name'] ?? '—') ?></td>
                            <td><?= !empty($document['uploaded_at']) ? date('F j, Y', strtotime($document['uploaded_at'])) : '' ?></td>
                            <td>
                                <a
                                    href="<?php echo htmlspecialchars(BASE_URL . $fileUrl($document['file_path'])); ?>"
                                    target="_blank"
                                    class="btn btn-sm btn-outline-secondary"
                                >
                                    <i class="bx bx-show"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No student documents found.</td>
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
                    <?php $qs = function ($p) use ($status_filter) { return '?' . http_build_query(['status' => $status_filter, 'page' => $p]); }; ?>
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

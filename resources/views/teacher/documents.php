<?php
require_once __DIR__ . '/../../../app/controllers/teacher/StudentsDocumentController.php';
require_once __DIR__ . '/../../../app/helpers/flashMessage.php';
require_once __DIR__ . '/../../../app/middleware/Auth.php';
AuthRole::allowOnly(['teacher']);

$e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$section = $teacherDocumentsData['section'] ?? null;
$students = $teacherDocumentsData['students'] ?? [];
$documents = $teacherDocumentsData['documents'] ?? [];
$documentTypes = $teacherDocumentsData['document_types'] ?? [];

$statusFilter = $_GET['status'] ?? '';
$allowedStatuses = ['Submitted', 'Pending', 'Verified', 'Rejected'];
if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = '';
}

$statusMeta = function (string $status): array {
    return match ($status) {
        'Verified'  => ['bg-label-success', 'bx-check-circle'],
        'Submitted' => ['bg-label-info', 'bx-upload'],
        'Pending'   => ['bg-label-warning', 'bx-time-five'],
        'Rejected'  => ['bg-label-danger', 'bx-x-circle'],
        default     => ['bg-label-secondary', 'bx-file'],
    };
};

$fileUrl = function (?string $path): string {
    if (empty($path)) {
        return '#';
    }
    return '/storage/student_documents/' . basename($path);
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
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />
    <title>Documents | <?php  require_once __DIR__ . '/../../../app/helpers/title.php'; ?></title>
    <meta name="csrf-token" content="<?php echo htmlspecialchars(Csrf::token()); ?>">
    <meta name="description" content="" />
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
</head>
<body>

    <?php FlashMessage::showFlash(); ?>

    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
    <?php require_once __DIR__ . '/partials/topbar.php'; ?>

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-0">Documents</h4>
                <small class="text-muted">Requirements submitted for your students</small>
            </div>
            <?php if ($section): ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                    <i class="bx bx-plus"></i> Upload Document
                </button>
            <?php endif; ?>
        </div>

        <?php if (!$section): ?>
            <div class="alert alert-warning">
                <i class="bx bx-error-circle me-1"></i>
                You are not currently assigned as a section adviser for the active school year.
            </div>
        <?php else: ?>

            <div class="card" id="documentsCard">
                <h5 class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span id="documentsCardHeader">Student Documents<?= $statusFilter !== '' ? ' — ' . $e($statusFilter) : '' ?></span>
                    <?php if ($statusFilter !== ''): ?>
                        <a href="documents.php" class="btn btn-sm btn-outline-secondary">Clear Filter</a>
                    <?php endif; ?>
                </h5>
                <div class="table-responsive nowrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student Name</th>
                                <th>Document Type</th>
                                <th>Status</th>
                                <th>Uploaded At</th>
                                <th>File</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $visibleDocuments = $statusFilter !== ''
                                    ? array_values(array_filter($documents, fn($d) => $d['status'] === $statusFilter))
                                    : $documents;
                            ?>
                            <?php if (!empty($visibleDocuments)): ?>
                                <?php foreach ($visibleDocuments as $index => $document): ?>
                                    <?php
                                        $studentFullName = $document['student_last_name'] . ', ' . $document['student_first_name'];
                                        [$statusBadgeClass, $statusIcon] = $statusMeta($document['status']);
                                    ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td class="fw-semibold"><?= $e($studentFullName) ?></td>
                                        <td><?= $e($document['document_type_name']) ?></td>
                                        <td>
                                            <span class="badge <?= $statusBadgeClass ?>">
                                                <i class="bx <?= $statusIcon ?> me-1"></i><?= $e($document['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($document['uploaded_at'])) ?></td>
                                        <td>
                                            <a
                                                href="<?= $e(BASE_URL . $fileUrl($document['file_path'])) ?>"
                                                target="_blank"
                                                class="btn btn-sm btn-outline-secondary"
                                                title="View File"
                                                aria-label="View"
                                            >
                                                <i class="bx bx-show"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="bx bx-folder-open fs-1 d-block mb-2"></i>
                                        No documents found.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Upload Document Modal -->
            <div class="modal fade" id="uploadDocumentModal" tabindex="-1" aria-labelledby="uploadDocumentModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header py-2">
                            <h6 class="modal-title fw-bold mb-0" id="uploadDocumentModalLabel">Upload Document</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="<?= BASE_URL ?>/app/controllers/teacher/StudentsDocumentController.php" method="post" enctype="multipart/form-data">
                            <?php echo Csrf::field(); ?>
                            <div class="modal-body py-2">
                                <p class="text-muted small">
                                    Uploaded documents are automatically marked <strong>Pending</strong> and reviewed by the registrar.
                                </p>
                                <div class="mb-3">
                                    <label class="form-label small mb-1 fw-bold">Student: <span class="text-danger">*</span></label>
                                    <select class="form-control form-control-sm" name="student_id" required>
                                        <option value="">-- Select Student --</option>
                                        <?php foreach ($students as $student): ?>
                                            <option value="<?= (int) $student['student_id'] ?>">
                                                <?= $e($student['last_name'] . ', ' . $student['first_name']) ?> (LRN: <?= $e($student['lrn']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small mb-1 fw-bold">Document Type: <span class="text-danger">*</span></label>
                                    <select class="form-control form-control-sm" name="document_type_id" required>
                                        <option value="">-- Select Document Type --</option>
                                        <?php foreach ($documentTypes as $type): ?>
                                            <option value="<?= (int) $type['id'] ?>"><?= $e($type['document_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small mb-1 fw-bold">File: <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control form-control-sm" name="file_path" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                    <small class="text-muted">Allowed formats: PDF, DOC, DOCX, JPG, PNG</small>
                                </div>
                            </div>
                            <div class="modal-footer py-1">
                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-sm btn-primary px-3" name="upload_document">Upload</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>

    <?php require_once __DIR__ . '/partials/footer.php'; ?>

    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/popper/popper.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/js/bootstrap.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/js/menu.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/main.js"></script>
    <script async defer src="https://buttons.github.io/buttons.js"></script>
</body>
</html>

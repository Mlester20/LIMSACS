<?php
require_once __DIR__ . '/../../../app/helpers/flashMessage.php';
require_once __DIR__ . '/../../../app/controllers/registrar/StudentsDocumentController.php';
require_once __DIR__ . '/../../../database/config/config.php';
require_once __DIR__ . '/../../../app/middleware/Auth.php';
AuthRole::allowOnly(['registrar']);

try {
    $controller = new StudentsDocumentController($con);
    $documents = $controller->index();
    $documentTypes = $controller->getDocumentTypes();
} catch (Exception $e) {
    error_log($e->getMessage());
    $documents = [];
    $documentTypes = [];
}

// Status -> [badge class, icon] used for stat cards and table badges.
$statusMeta = function (string $status): array {
    return match ($status) {
        'Verified'  => ['bg-label-success', 'bx-check-circle'],
        'Submitted' => ['bg-label-info', 'bx-upload'],
        'Pending'   => ['bg-label-warning', 'bx-time-five'],
        'Rejected'  => ['bg-label-danger', 'bx-x-circle'],
        default     => ['bg-label-secondary', 'bx-file'],
    };
};

// File extension -> icon class for the document type column.
$fileIcon = function (?string $path): string {
    $ext = strtolower(pathinfo((string) $path, PATHINFO_EXTENSION));
    return match ($ext) {
        'pdf'          => 'bx-file-pdf text-danger',
        'doc', 'docx'  => 'bx-file-doc text-primary',
        'xls', 'xlsx'  => 'bx-spreadsheet text-success',
        'jpg', 'jpeg', 'png' => 'bx-image text-warning',
        default        => 'bx-file text-secondary',
    };
};

// Normalize the stored relative path into the absolute /storage/... URL used for previews.
$fileUrl = function (?string $path): string {
    if (empty($path)) {
        return '#';
    }
    return '/storage/student_documents/' . basename($path);
};

$statusCounts = ['Submitted' => 0, 'Verified' => 0, 'Pending' => 0, 'Rejected' => 0];
foreach ($documents as $doc) {
    if (isset($statusCounts[$doc['status']])) {
        $statusCounts[$doc['status']]++;
    }
}
$totalDocuments = count($documents);
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
    <title>Student Documents | <?php  require_once __DIR__ . '/../../../app/helpers/title.php'; ?></title>
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

    <?php FlashMessage::showFlash(); ?>

    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
    <?php require_once __DIR__ . '/partials/topbar.php'; ?>

    <!-- ── Page header ───────────────────────────────────────────────────── -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-0">Student Documents</h4>
            <small class="text-muted">Track and manage submitted, verified, and pending student requirements</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDocumentModal">
                <i class="bx bx-plus"></i> Add Document
            </button>
        </div>
    </div>

    <!-- ── Summary cards ─────────────────────────────────────────────────── -->
    <div class="row g-4 mb-4">
        <div class="col-6 col-md-4 col-xl">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="avatar avatar-lg flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-primary">
                            <i class="bx bx-folder-open bx-sm"></i>
                        </span>
                    </div>
                    <div>
                        <p class="text-muted small mb-0">Total Documents</p>
                        <h3 class="mb-0 fw-bold"><?php echo number_format($totalDocuments); ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="avatar avatar-lg flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-success">
                            <i class="bx bx-check-circle bx-sm"></i>
                        </span>
                    </div>
                    <div>
                        <p class="text-muted small mb-0">Verified</p>
                        <h3 class="mb-0 fw-bold"><?php echo number_format($statusCounts['Verified']); ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="avatar avatar-lg flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-info">
                            <i class="bx bx-upload bx-sm"></i>
                        </span>
                    </div>
                    <div>
                        <p class="text-muted small mb-0">Submitted</p>
                        <h3 class="mb-0 fw-bold"><?php echo number_format($statusCounts['Submitted']); ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="avatar avatar-lg flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-warning">
                            <i class="bx bx-time-five bx-sm"></i>
                        </span>
                    </div>
                    <div>
                        <p class="text-muted small mb-0">Pending</p>
                        <h3 class="mb-0 fw-bold"><?php echo number_format($statusCounts['Pending']); ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="avatar avatar-lg flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-danger">
                            <i class="bx bx-x-circle bx-sm"></i>
                        </span>
                    </div>
                    <div>
                        <p class="text-muted small mb-0">Rejected</p>
                        <h3 class="mb-0 fw-bold"><?php echo number_format($statusCounts['Rejected']); ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Search toolbar ────────────────────────────────────────────────── -->
    <div class="row mb-3 align-items-center">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text bg-label-secondary border-end-0"><i class="bx bx-search"></i></span>
                <input type="text" class="form-control border-start-0" placeholder="Search by student name or document type..." id="searchInput">
            </div>
        </div>
        <div class="col-md-6 text-end mt-2 mt-md-0">
            <span class="text-muted small">
                <i class="bx bx-folder me-1"></i>
                <?php echo number_format($totalDocuments); ?> document record<?php echo $totalDocuments === 1 ? '' : 's'; ?> total
            </span>
        </div>
    </div>

    <div class="card">
        <h5 class="card-header">Student Documents</h5>
        <div class="table-responsive nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><i class="bx bx-user me-1"></i>Student Name</th>
                        <th><i class="bx bx-file-blank me-1"></i>Document Type</th>
                        <th><i class="bx bx-flag me-1"></i>Status</th>
                        <th><i class="bx bx-user-circle me-1"></i>Uploaded By</th>
                        <th><i class="bx bx-calendar me-1"></i>Uploaded At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="documentsTableBody">
                    <?php if(!empty($documents)): ?>
                        <?php
                            // Count how many document rows belong to each student so we
                            // know the rowspan to use on that student's name cell.
                            $studentRowspans = [];
                            foreach($documents as $document){
                                $studentRowspans[$document['student_id']] = ($studentRowspans[$document['student_id']] ?? 0) + 1;
                            }
                            $lastStudentId = null;
                        ?>
                        <?php foreach($documents as $index => $document): ?>
                            <?php
                                $isNewStudentGroup = $document['student_id'] !== $lastStudentId;
                                $lastStudentId = $document['student_id'];
                                $studentFullName = $document['student_first_name'] . ' ' . $document['student_last_name'];
                                [$statusBadgeClass, $statusIcon] = $statusMeta($document['status']);
                            ?>
                            <tr
                                class="document-row"
                                <?php if($isNewStudentGroup && $index > 0): ?>style="border-top: 2px solid #e9ecef;"<?php endif; ?>
                                data-student-id="<?php echo htmlspecialchars($document['student_id']); ?>"
                                data-student-name="<?php echo htmlspecialchars(strtolower($studentFullName)); ?>"
                                data-document-type="<?php echo htmlspecialchars(strtolower($document['document_type_name'])); ?>"
                            >
                                <td><?php echo $index + 1; ?></td>
                                <?php if($isNewStudentGroup): ?>
                                    <td rowspan="<?php echo $studentRowspans[$document['student_id']]; ?>" class="align-middle">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar avatar-sm flex-shrink-0">
                                                <span class="avatar-initial rounded-circle bg-label-primary">
                                                    <?php echo htmlspecialchars(strtoupper(substr($studentFullName, 0, 1))); ?>
                                                </span>
                                            </div>
                                            <span class="fw-medium"><?php echo htmlspecialchars($studentFullName); ?></span>
                                        </div>
                                    </td>
                                <?php endif; ?>
                                <td>
                                    <i class="bx <?php echo $fileIcon($document['file_path']); ?> me-1"></i>
                                    <?php echo htmlspecialchars($document['document_type_name']); ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $statusBadgeClass; ?>">
                                        <i class="bx <?php echo $statusIcon; ?> me-1"></i><?php echo htmlspecialchars($document['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($document['uploaded_by_name'] ?? '—'); ?></td>
                                <td><?php echo date('M d, Y', strtotime($document['uploaded_at'])); ?></td>
                                <td class="text-nowrap">
                                    <a
                                        href="<?php echo htmlspecialchars($fileUrl($document['file_path'])); ?>"
                                        target="_blank"
                                        class="btn btn-sm btn-outline-secondary"
                                        title="View File"
                                    >
                                        <i class="bx bx-show"></i>
                                    </a>
                                    <button class="btn btn-sm btn-warning" title="Edit" data-bs-toggle="modal" data-bs-target="#editDocumentModal" onclick="editDocument(<?php echo htmlspecialchars(json_encode($document)); ?>)">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" title="Delete" onclick="deleteDocument(<?php echo $document['id']; ?>)">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bx bx-folder-open fs-1 d-block mb-2"></i>
                                No documents found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <div class="card-footer py-3" id="paginationContainer">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-2">
                        <label for="itemsPerPage" class="mb-0 text-muted small">Show:</label>
                        <select id="itemsPerPage" class="form-select form-select-sm" style="width: auto;">
                            <option value="10">10</option>
                            <option value="15" selected>15</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="text-muted small ms-2">entries | Showing <strong id="showingStart">1</strong> to <strong id="showingEnd">15</strong> of <strong id="totalEntries">0</strong></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <nav aria-label="Pagination Navigation">
                        <ul class="pagination justify-content-end mb-0" id="paginationList">
                            <!-- Pagination buttons will be generated here by JavaScript -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Document Modal -->
    <div class="modal fade" id="addDocumentModal" tabindex="-1" aria-labelledby="addDocumentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 rounded-2" style="width:34px;height:34px;">
                            <i class="bx bx-plus text-primary fs-5"></i>
                        </div>
                        <h6 class="modal-title fw-bold mb-0" id="addDocumentModalLabel">Add New Document</h6>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../../../app/controllers/registrar/StudentsDocumentController.php" method="post" enctype="multipart/form-data" id="addDocumentForm">
                    <?php echo Csrf::field(); ?>
                    <div class="modal-body py-2">
                        <!-- Student Search -->
                        <div class="mb-3">
                            <label class="form-label small mb-1 fw-bold">Student: <span class="text-danger">*</span></label>
                            <input type="hidden" id="add_student_id" name="student_id" required>
                            <input
                                type="text"
                                class="form-control form-control-sm"
                                id="addStudentSearch"
                                placeholder="Search by name or LRN (minimum 2 characters)..."
                                autocomplete="off"
                            >
                            <div id="addStudentResults" class="list-group list-group-sm mt-1" style="
                                max-height: 200px;
                                overflow-y: auto;
                                display: none;
                                border: 1px solid #dee2e6;
                                border-top: none;
                            "></div>
                        </div>

                        <!-- Document Type -->
                        <div class="mb-3">
                            <label class="form-label small mb-1 fw-bold">Document Type: <span class="text-danger">*</span></label>
                            <select class="form-control form-control-sm" name="document_type_id" required>
                                <option value="">-- Select Document Type --</option>
                                <?php foreach($documentTypes as $type): ?>
                                    <option value="<?php echo $type['id']; ?>"><?php echo htmlspecialchars($type['document_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Document File -->
                        <div class="mb-3">
                            <label class="form-label small mb-1 fw-bold">Document File: <span class="text-danger">*</span></label>
                            <input type="file" class="form-control form-control-sm" name="file_path" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <small class="text-muted">Allowed formats: PDF, DOC, DOCX, JPG, PNG</small>
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label class="form-label small mb-1 fw-bold">Status:</label>
                            <select class="form-control form-control-sm" name="status">
                                <option value="Submitted" selected>Submitted</option>
                                <option value="Pending">Pending</option>
                                <option value="Verified">Verified</option>
                                <option value="Rejected">Rejected</option>
                            </select>
                        </div>

                        <!-- Remarks -->
                        <div class="mb-3">
                            <label class="form-label small mb-1 fw-bold">Remarks:</label>
                            <textarea class="form-control form-control-sm" name="remarks" rows="3" placeholder="Add any remarks..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer py-1">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-sm btn-primary px-3" name="submit_document">Save Document</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<div class="modal fade" id="editDocumentModal" tabindex="-1" aria-labelledby="editDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form action="../../../app/controllers/registrar/StudentsDocumentController.php" method="post" enctype="multipart/form-data" id="editDocumentForm">
                <?php echo Csrf::field(); ?>

                <div class="modal-header py-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="d-flex align-items-center justify-content-center bg-warning bg-opacity-10 rounded-2" style="width:34px;height:34px;">
                            <i class="bx bx-edit text-warning fs-5"></i>
                        </div>
                        <h6 class="modal-title fw-bold mb-0" id="editDocumentModalLabel">Edit Document</h6>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <input type="hidden" name="document_id" id="edit_document_id">

                <div class="modal-body py-2" style="max-height: 60vh; overflow-y: auto;">
                    <div class="mb-3">
                        <label class="form-label small mb-1 fw-bold">Student: <span class="text-danger">*</span></label>
                        <input type="hidden" id="edit_student_id" name="student_id" required>
                        <input
                            type="text"
                            class="form-control form-control-sm"
                            id="editStudentSearch"
                            placeholder="Search by name or LRN (minimum 2 characters)..."
                            autocomplete="off"
                        >
                        <div id="editStudentResults" class="list-group list-group-sm mt-1" style="
                            max-height: 200px;
                            overflow-y: auto;
                            display: none;
                            border: 1px solid #dee2e6;
                            border-top: none;
                        "></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small mb-1 fw-bold">Document Type: <span class="text-danger">*</span></label>
                        <select class="form-control form-control-sm" name="document_type_id" id="edit_document_type_id" required>
                            <option value="">-- Select Document Type --</option>
                            <?php foreach($documentTypes as $type): ?>
                                <option value="<?php echo $type['id']; ?>"><?php echo htmlspecialchars($type['document_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small mb-1 fw-bold">Document File:</label>
                        <div id="currentFileInfo" class="alert alert-info py-1 px-2 mb-2" style="display: none; font-size: 0.85rem;">
                            <small><strong>Current file:</strong> <span id="currentFileName"></span></small>
                        </div>
                        <div id="currentFilePreview" style="display:none; margin-top: 8px;"></div>
                        <input type="file"
                        id="edit_file_path"
                        class="form-control form-control-sm"
                        name="file_path"
                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        <small class="text-muted">Leave blank to keep current file. Upload a new file to replace it. Allowed formats: PDF, DOC, DOCX, JPG, PNG</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small mb-1 fw-bold">Status:</label>
                        <select class="form-control form-control-sm" name="status" id="edit_status">
                            <option value="Submitted">Submitted</option>
                            <option value="Pending">Pending</option>
                            <option value="Verified">Verified</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small mb-1 fw-bold">Remarks:</label>
                        <textarea class="form-control form-control-sm" name="remarks" id="edit_remarks" rows="3" placeholder="Add any remarks..."></textarea>
                    </div>
                </div>

                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-sm btn-primary px-3" name="update_document">Update Document</button>
                </div>

            </form>
        </div>
    </div>
</div>

    <!-- Delete Document Form (Hidden) -->
    <form action="../../../app/controllers/registrar/StudentsDocumentController.php" method="post" id="deleteDocumentForm" style="display: none;">
        <?php echo Csrf::field(); ?>
        <input type="hidden" name="document_id" id="delete_document_id">
        <input type="hidden" name="delete_document" value="1">
    </form>

    <?php require_once __DIR__ . '/partials/footer.php';?>

    <script src="../../../public/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../../../public/assets/vendor/libs/popper/popper.js"></script>
    <script src="../../../public/assets/vendor/js/bootstrap.js"></script>
    <script src="../../../public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../../../public/assets/vendor/js/menu.js"></script>
    <script src="../../../public/assets/js/main.js"></script>
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <script src="../../../public/js/registrar/student-documents.js"></script>
</body>
</html>

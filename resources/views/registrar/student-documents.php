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

    <div class="row mb-3 align-items-center">
        <div class="col-md-6">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Search documents by student name..." id="searchInput">
            </div>
        </div>
        <div class="col-md-6 text-end mt-2 mt-md-0">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDocumentModal">
                <i class="bx bx-plus"></i> Add Document
            </button>
        </div>
    </div>

    <div class="card">
        <h5 class="card-header">Student Documents</h5>
        <div class="table-responsive nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student Name</th>
                        <th>Document Type</th>
                        <th>Status</th>
                        <th>Uploaded By</th>
                        <th>Uploaded At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="documentsTableBody">
                    <?php if(!empty($documents)): ?>
                        <?php foreach($documents as $index => $document): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($document['student_first_name'] . ' ' . $document['student_last_name']); ?></td>
                                <td><?php echo htmlspecialchars($document['document_type_name']); ?></td>
                                <td>
                                    <?php 
                                        $status = $document['status'];
                                        $statusClass = match($status) {
                                            'Submitted' => 'bg-info',
                                            'Verified' => 'bg-success',
                                            'Rejected' => 'bg-danger',
                                            'Pending' => 'bg-warning',
                                            default => 'bg-secondary'
                                        };
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($status); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($document['uploaded_by_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($document['uploaded_at'])); ?></td>
                                <td>
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
                            <td colspan="7" class="text-center text-muted py-3">No documents found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Document Modal -->
    <div class="modal fade" id="addDocumentModal" tabindex="-1" aria-labelledby="addDocumentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title fw-bold" id="addDocumentModalLabel">Add New Document</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../../../app/controllers/registrar/StudentsDocumentController.php" method="post" enctype="multipart/form-data" id="addDocumentForm">
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
                
                <div class="modal-header py-2">
                    <h6 class="modal-title fw-bold" id="editDocumentModalLabel">Edit Document</h6>
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
<?php
require_once __DIR__ . '/../../../app/controllers/teacher/StudentController.php';
require_once __DIR__ . '/../../../app/helpers/flashMessage.php';
require_once __DIR__ . '/../../../app/middleware/auth.php';
AuthRole::allowOnly(['teacher']);
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
    <title> Students List | <?php require_once __DIR__ . '/../../../app/helpers/title.php'; ?> </title>
    <meta name="csrf-token" content="<?php echo htmlspecialchars(Csrf::token()); ?>">
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

    <?php FlashMessage::showFlash(); ?>

    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
    <?php require_once __DIR__ . '/partials/topbar.php'; ?>

    <div class="card">
        <h5 class="card-header">My Students</h5>
        <div class="table-responsive nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>LRN</th>
                        <th>Full Name</th>
                        <th>Gender</th>
                        <th>Age</th>
                        <th>Grade & Section</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($students)): ?>
                        <?php foreach($students as $index => $student): ?>
                            <tr
                                style="cursor: pointer;"
                                data-bs-toggle="modal"
                                data-bs-target="#studentDetailsModal"
                                onclick="populateStudentModal(<?php echo htmlspecialchars(json_encode($student)); ?>)"
                            >
                                <td><?php echo $pagination['itemsPerPage'] * ($pagination['currentPage'] - 1) + ($index + 1); ?></td>
                                <td><?php echo htmlspecialchars($student['lrn'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($student['full_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($student['gender'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($student['age'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars(trim(($student['grade_level'] ?? '') . ' - ' . ($student['section_name'] ?? ''), ' -')) ?: 'N/A'; ?></td>
                                <td>
                                    <?php
                                        $badgeColors = ['Enrolled' => 'success', 'Graduated' => 'primary', 'Transferred' => 'warning', 'Dropped' => 'danger'];
                                        $rowBadgeColor = $badgeColors[$student['enrollment_status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo $rowBadgeColor; ?>"><?php echo htmlspecialchars($student['enrollment_status'] ?? 'N/A'); ?></span>
                                </td>
                                <td>
                                    <button
                                        class="btn btn-sm btn-primary"
                                        title="Edit Student"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editStudentModal"
                                        onclick="event.stopPropagation(); editStudent(<?php echo htmlspecialchars(json_encode($student)); ?>)">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>
                                    <?php if(($student['enrollment_status'] ?? '') === 'Enrolled'): ?>
                                        <?php $fullNameJs = htmlspecialchars($student['full_name'], ENT_QUOTES); ?>
                                        <button
                                            class="btn btn-sm btn-danger"
                                            title="Mark as Dropped"
                                            onclick="event.stopPropagation(); studentsController.dropStudent(<?php echo (int)$student['enrollment_id']; ?>, '<?php echo $fullNameJs; ?>')">
                                            <i class="bx bx-x-circle"></i>
                                        </button>
                                        <button
                                            class="btn btn-sm btn-warning"
                                            title="Mark as Transferred"
                                            onclick="event.stopPropagation(); studentsController.transferStudent(<?php echo (int)$student['enrollment_id']; ?>, '<?php echo $fullNameJs; ?>')">
                                            <i class="bx bx-transfer"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No Student Currently Assigned To You</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">
                        Showing <?php echo $pagination['totalRecords'] > 0 ? (($pagination['currentPage'] - 1) * $pagination['itemsPerPage']) + 1 : 0; ?>
                        to <?php echo min($pagination['currentPage'] * $pagination['itemsPerPage'], $pagination['totalRecords']); ?>
                        of <?php echo $pagination['totalRecords']; ?> records
                    </small>
                </div>

                <nav>
                    <ul class="pagination pagination-sm m-0">
                        <!-- Previous Button -->
                        <?php if($pagination['hasPrevPage']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $pagination['currentPage'] - 1; ?>">Previous</a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link">Previous</span>
                            </li>
                        <?php endif; ?>

                        <!-- Page Numbers -->
                        <?php
                            $startPage = max(1, $pagination['currentPage'] - 2);
                            $endPage = min($pagination['totalPages'], $pagination['currentPage'] + 2);

                            if($startPage > 1):
                        ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=1">1</a>
                            </li>
                            <?php if($startPage > 2): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for($i = $startPage; $i <= $endPage; $i++): ?>
                            <?php if($i == $pagination['currentPage']): ?>
                                <li class="page-item active">
                                    <span class="page-link"><?php echo $i; ?></span>
                                </li>
                            <?php else: ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if($endPage < $pagination['totalPages']): ?>
                            <?php if($endPage < $pagination['totalPages'] - 1): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $pagination['totalPages']; ?>"><?php echo $pagination['totalPages']; ?></a>
                            </li>
                        <?php endif; ?>

                        <!-- Next Button -->
                        <?php if($pagination['hasNextPage']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $pagination['currentPage'] + 1; ?>">Next</a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link">Next</span>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Update Student Modal -->
    <div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="editStudentForm" method="POST" action="../../../app/controllers/teacher/StudentController.php">
                <?php echo Csrf::field(); ?>
                <div class="modal-content">
                    <div class="modal-header py-2">
                        <h6 class="modal-title mb-0" id="editStudentModalLabel">
                            <i class="icon-base iconify" data-icon="tabler:user-edit"></i> Edit Student
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-3">

                        <!-- Hidden Student ID -->
                        <input type="hidden" id="editStudentId" name="student_id" value="">

                        <p class="text-muted small mb-2"><em>Note: Leave blank if N/A</em></p>

                        <p class="text-muted fw-semibold mb-2" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:.05em;">Student Identity</p>
                        <div class="row g-2 mb-3">
                            <div class="col-md-12">
                                <label class="form-label form-label-sm mb-1">LRN (Learner Reference Number)</label>
                                <input type="text" class="form-control form-control-sm" id="editLrn" name="lrn" maxlength="20">
                            </div>
                        </div>

                        <p class="text-muted fw-semibold mb-2" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:.05em;">Personal Information</p>
                        <div class="row g-2 mb-2">
                            <div class="col-md-3">
                                <label class="form-label form-label-sm mb-1">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="editFirstName" name="first_name" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label form-label-sm mb-1">Middle Name</label>
                                <input type="text" class="form-control form-control-sm" id="editMiddleName" name="middle_name">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label form-label-sm mb-1">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="editLastName" name="last_name" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label form-label-sm mb-1">Suffix</label>
                                <input type="text" class="form-control form-control-sm" id="editSuffix" name="suffix" placeholder="Jr., Sr., III">
                            </div>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-md-12">
                                <label class="form-label form-label-sm mb-1">Gender <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" id="editGender" name="gender" required>
                                    <option value="" disabled>Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-3">
                                <label class="form-label form-label-sm mb-1">Birth Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control form-control-sm" id="editBirthDate" name="birth_date" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label form-label-sm mb-1">Age</label>
                                <input type="number" class="form-control form-control-sm" id="editAge" name="age" min="1" max="30">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label form-label-sm mb-1">Place of Birth</label>
                                <input type="text" class="form-control form-control-sm" id="editPlaceOfBirth" name="place_of_birth" maxlength="150">
                            </div>
                        </div>

                        <p class="text-muted fw-semibold mb-2" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:.05em;">Demographics</p>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label form-label-sm mb-1">Nationality</label>
                                <input type="text" class="form-control form-control-sm" id="editNationality" name="nationality" maxlength="100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label form-label-sm mb-1">Religion</label>
                                <input type="text" class="form-control form-control-sm" id="editReligion" name="religion" maxlength="100">
                            </div>
                        </div>

                        <p class="text-muted fw-semibold mb-2" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:.05em;">Contact & Address</p>
                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <label class="form-label form-label-sm mb-1">Contact Number</label>
                                <input type="text" class="form-control form-control-sm" id="editContactNumber" name="contact_number" maxlength="20">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label form-label-sm mb-1">Address</label>
                                <input type="text" class="form-control form-control-sm" id="editAddress" name="address">
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="editStudentBtn" name="edit_student">
                            <i class="icon-base iconify" data-icon="tabler:device-floppy"></i> Update Student
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Student Profile Modal -->
    <div class="modal fade" id="studentDetailsModal" tabindex="-1" aria-labelledby="studentDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header text-white">
                    <h5 class="modal-title" id="studentDetailsModalLabel">Student Profile</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">

                    <ul class="nav nav-tabs mb-3" id="studentDetailsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="studentProfileTab-tab" data-bs-toggle="tab" data-bs-target="#studentProfileTab" type="button" role="tab" aria-controls="studentProfileTab" aria-selected="true">Profile</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="studentAcademicHistoryTab-tab" data-bs-toggle="tab" data-bs-target="#studentAcademicHistoryTab" type="button" role="tab" aria-controls="studentAcademicHistoryTab" aria-selected="false">Academic History</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="studentParentGuardiansTab-tab" data-bs-toggle="tab" data-bs-target="#studentParentGuardiansTab" type="button" role="tab" aria-controls="studentParentGuardiansTab" aria-selected="false">Parents / Guardians</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="studentDocumentsTab-tab" data-bs-toggle="tab" data-bs-target="#studentDocumentsTab" type="button" role="tab" aria-controls="studentDocumentsTab" aria-selected="false">Documents</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="studentDetailsTabContent">

                        <div class="tab-pane fade show active" id="studentProfileTab" role="tabpanel" aria-labelledby="studentProfileTab-tab">

                            <p class="text-muted small text-uppercase mb-3" style="letter-spacing: 0.5px;">Student Identity</p>
                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <label class="d-block small text-muted mb-1">LRN</label>
                                    <div id="modalLrn">-</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="d-block small text-muted mb-1">Status</label>
                                    <span id="modalStatusBadge" class="badge bg-success">Active</span>
                                </div>
                            </div>

                            <p class="text-muted small text-uppercase mb-3 pt-3 border-top" style="letter-spacing: 0.5px;">Personal Information</p>
                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <label class="d-block small text-muted mb-1">First Name</label>
                                    <div id="modalFirstName">-</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="d-block small text-muted mb-1">Middle Name</label>
                                    <div id="modalMiddleName">-</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="d-block small text-muted mb-1">Last Name</label>
                                    <div id="modalLastName">-</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="d-block small text-muted mb-1">Gender</label>
                                    <div id="modalGender">-</div>
                                </div>

                                <div class="col-md-3">
                                    <label class="d-block small text-muted mb-1">Birth Date</label>
                                    <div id="modalBirthDate">-</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="d-block small text-muted mb-1">Age</label>
                                    <div id="modalAge">-</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="d-block small text-muted mb-1">Place of Birth</label>
                                    <div id="modalPlaceOfBirth">-</div>
                                </div>
                            </div>

                            <p class="text-muted small text-uppercase mb-3 pt-3 border-top" style="letter-spacing: 0.5px;">Demographics & Contact</p>
                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <label class="d-block small text-muted mb-1">Religion</label>
                                    <div id="modalReligion">-</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="d-block small text-muted mb-1">Contact Number</label>
                                    <div id="modalContactNumber">-</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="d-block small text-muted mb-1">Address</label>
                                    <div id="modalAddress" class="text-break">-</div>
                                </div>
                            </div>

                        </div>

                        <div class="tab-pane fade" id="studentAcademicHistoryTab" role="tabpanel" aria-labelledby="studentAcademicHistoryTab-tab">
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>School Year</th>
                                            <th>Grade & Section</th>
                                            <th>Adviser Name</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="academicHistoryBody">
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Loading...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="studentParentGuardiansTab" role="tabpanel" aria-labelledby="studentParentGuardiansTab-tab">
                            <div class="d-flex justify-content-end mb-2">
                                <a href="parent-guardians.php" class="btn btn-sm btn-outline-primary">
                                    <i class="bx bx-group"></i> Manage Parents/Guardians
                                </a>
                            </div>
                            <div id="parentGuardiansInfo">
                                <p class="text-center text-muted">Loading...</p>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="studentDocumentsTab" role="tabpanel" aria-labelledby="studentDocumentsTab-tab">
                            <p class="small text-muted mb-3" id="documentChecklistSummary">Loading documents...</p>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Document Type</th>
                                            <th>Status</th>
                                            <th>Remarks</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="documentChecklistBody">
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Loading...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Viewer Modal -->
    <div class="modal fade" id="documentViewerModal" tabindex="-1" aria-labelledby="documentViewerLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title mb-0" id="documentViewerLabel">Document Viewer</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" style="height: 75vh;">
                    <iframe id="documentViewerFrame" src="about:blank" style="width: 100%; height: 100%; border: 0;"></iframe>
                </div>
                <div class="modal-footer py-2">
                    <a id="documentViewerOpenNewTab" href="#" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">Open in New Tab</a>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/partials/footer.php'; ?>

    <!-- ── Vendor scripts ── -->
    <script src="../../../public/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../../../public/assets/vendor/libs/popper/popper.js"></script>
    <script src="../../../public/assets/vendor/js/bootstrap.js"></script>
    <script src="../../../public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../../../public/assets/vendor/js/menu.js"></script>
    <script src="../../../public/assets/js/main.js"></script>
    <script src="../../../public/js/teacher/students.js"></script>
</body>
</html>

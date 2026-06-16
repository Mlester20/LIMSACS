<?php
require_once __DIR__ . '/../../../database/config/config.php';
require_once __DIR__ . '/../../../app/controllers/registrar/EnrollmentController.php';
require_once __DIR__ . '/../../../app/middleware/Auth.php';
require_once __DIR__ . '/../../../app/helpers/flashMessage.php';

AuthRole::allowOnly(['registrar']);

try {
    $controller = new EnrollmentController($con);
    $schoolYears = $controller->getSchoolYears();
    $gradeLevels = $controller->getGradeLevels();
    
    // Get current page from query parameter
    $currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    
    // Get enrolled students with pagination
    $paginatedData = $controller->getEnrolledStudentsWithPagination($currentPage, 10);
    $enrollments = $paginatedData['enrollments'];
    $pagination = $paginatedData['pagination'];
} catch (Exception $e) {
    error_log($e->getMessage());
    $enrollments = [];
    $pagination = [
        'currentPage' => 1,
        'itemsPerPage' => 10,
        'totalRecords' => 0,
        'totalPages' => 0,
        'hasPrevPage' => false,
        'hasNextPage' => false
    ];
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Enrollment | <?php require_once __DIR__ . '/../../../app/helpers/title.php'; ?></title>
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
    <!-- Display flash messages -->
    <?php FlashMessage::showFlash(); ?>

    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
    <?php require_once __DIR__ . '/partials/topbar.php'; ?>

    <!-- Main content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Enrollment /</span> Student Enrollment</h4>
        <div class="row mb-3 align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search Enrolled Students..." id="searchInput">
                </div>
            </div>
            <div class="col-md-6 text-end mt-2 mt-md-0">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#enrollmentFormModal">
                    <i class="bx bx-plus"></i> Enroll Student
                </button>
            </div>
        </div>

        <div class="card">
            <h5 class="card-header">Enrolled Students</h5>
            <div class="table-responsive nowrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>LRN</th>
                            <th>School Year</th>
                            <th>Grade Level</th>
                            <th>Section</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="enrolledStudentsBody">
                        <?php if(!empty($enrollments)): ?>
                            <?php foreach($enrollments as $index => $enrollment): ?>
                                <tr>
                                    <td><?php echo $pagination['itemsPerPage'] * ($pagination['currentPage'] - 1) + ($index + 1); ?></td>
                                    <td><?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($enrollment['lrn']); ?></td>
                                    <td><?php echo htmlspecialchars($enrollment['school_year']); ?></td>
                                    <td><?php echo htmlspecialchars($enrollment['grade_level']); ?></td>
                                    <td><?php echo htmlspecialchars($enrollment['section_name']); ?></td>
                                    <td>
                                        <span class="badge bg-success"><?php echo htmlspecialchars($enrollment['enrollment_status']); ?></span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" title="View History" data-bs-toggle="modal" data-bs-target="#enrollmentHistoryModal" onclick="enrollmentController.showEnrollmentHistory(<?php echo $enrollment['student_id']; ?>, '<?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?>')">
                                            <i class="bx bx-history"></i>
                                        </button>
                                        <!-- <button class="btn btn-sm btn-warning" title="Edit">
                                            <i class="bx bx-edit"></i>
                                        </button> -->
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-3">No enrolled students found.</td>
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
                            Showing <?php echo (($pagination['currentPage'] - 1) * $pagination['itemsPerPage']) + 1; ?> 
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
    </div>

    <!-- Modal: Enrollment Form -->
    <div class="modal fade" id="enrollmentFormModal" tabindex="-1" aria-labelledby="enrollmentFormModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="enrollmentFormModalLabel">
                        <i class="bx bx-user-plus"></i> Enroll Student
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Step 1: Search Student -->
                    <div class="mb-4">
                        <h6 class="text-uppercase text-muted mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">Step 1: Search Student</h6>
                        <div class="row g-2">
                            <div class="col-md-12">
                                <label class="form-label form-label-sm mb-1">Search by LRN or Name</label>
                                <input type="text" class="form-control form-control-sm" id="searchStudent" placeholder="Enter LRN or full name..." autocomplete="off">
                            </div>
                        </div>
                        <div id="searchResults" class="mt-2"></div>
                    </div>

                    <hr class="my-4">

                    <!-- Step 2: Student Profile -->
                    <div id="studentProfileSection" style="display: none;">
                        <h6 class="text-uppercase text-muted mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">Step 2: Student Profile</h6>
                        <div id="studentProfile" class="mb-3 p-3" style="background: #f5f5f5; border-radius: 0.375rem;"></div>
                    </div>

                    <hr class="my-4" id="separator2" style="display: none;">

                    <!-- Step 3: Enrollment Details -->
                    <div id="enrollmentFormSection" style="display: none;">
                        <h6 class="text-uppercase text-muted mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">Step 3: Select Enrollment Details</h6>
                        <form id="enrollmentForm" method="POST" action="../../../app/controllers/registrar/EnrollmentController.php">
                            <input type="hidden" name="student_id" id="hiddenStudentId">

                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label form-label-sm mb-1">School Year <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm" id="schoolYear" name="school_year_id" required>
                                        <option value="" disabled selected>Select School Year</option>
                                        <?php foreach ($schoolYears as $sy): ?>
                                            <option value="<?= $sy['id'] ?>"><?= htmlspecialchars($sy['school_year']) ?> (<?= ucfirst($sy['status']) ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label form-label-sm mb-1">Grade Level <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm" id="gradeLevel" name="grade_level" required>
                                        <option value="" disabled selected>Select Grade Level</option>
                                        <?php foreach ($gradeLevels as $grade): ?>
                                            <option value="<?= htmlspecialchars($grade) ?>"><?= htmlspecialchars($grade) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-md-12">
                                    <label class="form-label form-label-sm mb-1">Section <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm" id="section" name="section_id" required>
                                        <option value="" disabled selected>Select Section</option>
                                    </select>
                                    <small class="text-muted d-block mt-1" id="sectionCapacityInfo"></small>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm" name="enroll_student">
                                    <i class="bx bx-check"></i> Enroll Student
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetForm()">
                                    <i class="bx bx-refresh"></i> Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Student Enrollment History -->
    <div class="modal fade" id="enrollmentHistoryModal" tabindex="-1" aria-labelledby="enrollmentHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="enrollmentHistoryModalLabel">
                        <i class="bx bx-history"></i> Enrollment History
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="enrollmentHistoryContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/partials/footer.php'; ?>

    <script src="../../../public/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../../../public/assets/vendor/libs/popper/popper.js"></script>
    <script src="../../../public/assets/vendor/js/bootstrap.js"></script>
    <script src="../../../public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../../../public/assets/vendor/js/menu.js"></script>
    <script src="../../../public/assets/js/main.js"></script>
    <script src="../../../public/js/registrar/enrollment.js"></script>
</body>
</html>

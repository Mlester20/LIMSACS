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
    $allSections = $controller->getAllSections();

    // Get current page from query parameter
    $currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

    // Get current status filter from query parameter
    $allowedStatusFilters = ['Enrolled', 'Transferred', 'Dropped', 'Graduated', 'All'];
    $statusFilter = $_GET['status'] ?? 'Enrolled';
    if (!in_array($statusFilter, $allowedStatusFilters, true)) {
        $statusFilter = 'Enrolled';
    }

    // Get current grade level filter from query parameter
    $gradeLevelFilter = $_GET['grade_level'] ?? '';
    if (!in_array($gradeLevelFilter, $gradeLevels, true)) {
        $gradeLevelFilter = '';
    }

    // Get current gender filter from query parameter
    $genderFilter = $_GET['gender'] ?? '';
    if (!in_array($genderFilter, ['Male', 'Female'], true)) {
        $genderFilter = '';
    }

    // Get current section filter from query parameter
    $sectionFilter = $_GET['section_id'] ?? '';

    // Get enrolled students with pagination
    $paginatedData = $controller->getEnrolledStudentsWithPagination($currentPage, 10, $statusFilter, $gradeLevelFilter, $genderFilter, $sectionFilter);
    $enrollments = $paginatedData['enrollments'];
    $pagination = $paginatedData['pagination'];
} catch (Exception $e) {
    error_log($e->getMessage());
    $enrollments = [];
    $statusFilter = $statusFilter ?? 'Enrolled';
    $gradeLevelFilter = $gradeLevelFilter ?? '';
    $genderFilter = $genderFilter ?? '';
    $sectionFilter = $sectionFilter ?? '';
    $gradeLevels = $gradeLevels ?? [];
    $schoolYears = $schoolYears ?? [];
    $allSections = $allSections ?? [];
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
  data-assets-path="<?= BASE_URL ?>/public/assets/"
  data-template="vertical-menu-template-free"
>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollees | <?php require_once __DIR__ . '/../../../app/helpers/title.php'; ?></title>
    <meta name="csrf-token" content="<?php echo htmlspecialchars(Csrf::token()); ?>">
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
    <!-- Display flash messages -->
    <?php FlashMessage::showFlash(); ?>

    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
    <?php require_once __DIR__ . '/partials/topbar.php'; ?>

    <!-- Main content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Enrollees /</span> Enrollees</h4>
        <div class="row mb-3 align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search Students..." id="searchInput" data-status-filter="<?= htmlspecialchars($statusFilter) ?>" data-grade-filter="<?= htmlspecialchars($gradeLevelFilter) ?>" data-gender-filter="<?= htmlspecialchars($genderFilter) ?>" data-section-filter="<?= htmlspecialchars($sectionFilter) ?>">
                </div>
            </div>
            <div class="col-md-6 mt-2 mt-md-0">
                <div class="d-flex flex-wrap justify-content-md-end gap-2">
                    <a href="<?= BASE_URL ?>/resources/views/registrar/enrollment-per-section.php" class="btn btn-outline-secondary">
                        <i class="bx bx-list-ul"></i> Per Section
                    </a>
                    <a href="<?= BASE_URL ?>/resources/views/registrar/promote-students.php" class="btn btn-outline-primary">
                        <i class="bx bx-up-arrow-alt"></i> Promote Students
                    </a>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#enrollmentFormModal">
                        <i class="bx bx-plus"></i> Enroll Student
                    </button>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <ul class="nav nav-pills mb-0" id="statusFilterTabs">
                <?php
                    $statusTabs = [
                        'Enrolled' => 'Enrolled',
                        'Transferred' => 'Transferred',
                        'Dropped' => 'Dropped',
                        'Graduated' => 'Graduated',
                        'All' => 'All'
                    ];
                    $tabQs = function ($tabStatus) use ($gradeLevelFilter, $genderFilter, $sectionFilter) {
                        return '?status=' . urlencode($tabStatus)
                            . '&grade_level=' . urlencode($gradeLevelFilter)
                            . '&gender=' . urlencode($genderFilter)
                            . '&section_id=' . urlencode($sectionFilter)
                            . '&page=1';
                    };
                ?>
                <?php foreach ($statusTabs as $tabStatus => $tabLabel): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $statusFilter === $tabStatus ? 'active' : '' ?>" href="<?= htmlspecialchars($tabQs($tabStatus)) ?>">
                            <?= htmlspecialchars($tabLabel) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="d-flex gap-2 flex-wrap">
                <select id="gradeLevelFilter" class="form-select form-select-sm" style="max-width: 200px;" onchange="applyEnrollmentFilters()">
                    <option value="">All Grade Levels</option>
                    <?php foreach ($gradeLevels as $gl): ?>
                        <option value="<?= htmlspecialchars($gl) ?>" <?= $gradeLevelFilter === $gl ? 'selected' : '' ?>><?= htmlspecialchars($gl) ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="sectionFilterSelect" class="form-select form-select-sm" style="max-width: 220px;" onchange="applyEnrollmentFilters()">
                    <option value="">All Sections</option>
                    <?php foreach ($allSections as $sec): ?>
                        <option value="<?= htmlspecialchars($sec['id']) ?>" <?= (string)$sectionFilter === (string)$sec['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sec['section_name'] . ' (' . $sec['grade_level'] . ')') ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="genderFilterSelect" class="form-select form-select-sm" style="max-width: 160px;" onchange="applyEnrollmentFilters()">
                    <option value="">All Genders</option>
                    <option value="Male" <?= $genderFilter === 'Male' ? 'selected' : '' ?>>Male</option>
                    <option value="Female" <?= $genderFilter === 'Female' ? 'selected' : '' ?>>Female</option>
                </select>
            </div>
        </div>

        <script>
            function applyEnrollmentFilters() {
                const status = <?= json_encode($statusFilter) ?>;
                const gradeLevel = document.getElementById('gradeLevelFilter').value;
                const sectionId = document.getElementById('sectionFilterSelect').value;
                const gender = document.getElementById('genderFilterSelect').value;
                window.location.href = '?status=' + encodeURIComponent(status)
                    + '&grade_level=' + encodeURIComponent(gradeLevel)
                    + '&section_id=' + encodeURIComponent(sectionId)
                    + '&gender=' + encodeURIComponent(gender)
                    + '&page=1';
            }
        </script>

        <div class="card">
            <h5 class="card-header">
                <?= htmlspecialchars($statusTabs[$statusFilter] ?? 'Enrolled') ?> Students
            </h5>
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
                    <tbody id="enrolledStudentsBody" data-terminal-grade="<?= htmlspecialchars(EnrollmentController::TERMINAL_GRADE_LEVEL) ?>">
                        <?php if(!empty($enrollments)): ?>
                            <?php foreach($enrollments as $index => $enrollment): ?>
                                <tr>
                                    <td><?php echo $pagination['itemsPerPage'] * ($pagination['currentPage'] - 1) + ($index + 1); ?></td>
                                    <td><?php echo htmlspecialchars($enrollment['last_name'] . ', ' . $enrollment['first_name']); ?></td>
                                    <td><?php echo htmlspecialchars($enrollment['lrn']); ?></td>
                                    <td><?php echo htmlspecialchars($enrollment['school_year']); ?></td>
                                    <td><?php echo htmlspecialchars($enrollment['grade_level']); ?></td>
                                    <td><?php echo htmlspecialchars($enrollment['section_name']); ?></td>
                                    <td>
                                        <?php
                                            $badgeColors = ['Enrolled' => 'success', 'Graduated' => 'primary', 'Transferred' => 'warning', 'Dropped' => 'danger'];
                                            $rowBadgeColor = $badgeColors[$enrollment['enrollment_status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $rowBadgeColor; ?>"><?php echo htmlspecialchars($enrollment['enrollment_status']); ?></span>
                                    </td>
                                    <td>
                                        <?php $fullNameJs = htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name'], ENT_QUOTES); ?>
                                        <div class="dropdown d-inline-block">
                                            <button class="btn btn-sm btn-icon btn-outline-secondary" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-expanded="false" title="Actions" aria-label="Actions">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#enrollmentHistoryModal" onclick="enrollmentController.showEnrollmentHistory(<?php echo $enrollment['student_id']; ?>, '<?php echo $fullNameJs; ?>')">
                                                        <i class="bx bx-history me-2"></i>View History
                                                    </a>
                                                </li>
                                                <?php if ($enrollment['enrollment_status'] === 'Enrolled'): ?>
                                                    <li>
                                                        <a class="dropdown-item" href="#" onclick="enrollmentController.openEditEnrollment(<?php echo $enrollment['enrollment_id']; ?>, <?php echo (int) $enrollment['school_year_id']; ?>, '<?php echo htmlspecialchars($enrollment['grade_level'], ENT_QUOTES); ?>', <?php echo (int) ($enrollment['section_id'] ?? 0); ?>, '<?php echo $fullNameJs; ?>', '<?php echo htmlspecialchars($enrollment['lrn'] ?? '', ENT_QUOTES); ?>')">
                                                            <i class="bx bx-edit me-2"></i>Edit Enrollment
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" onclick="enrollmentController.transferStudent(<?php echo $enrollment['enrollment_id']; ?>, '<?php echo $fullNameJs; ?>')">
                                                            <i class="bx bx-transfer me-2"></i>Mark as Transferred
                                                        </a>
                                                    </li>
                                                    <?php if ($enrollment['grade_level'] === EnrollmentController::TERMINAL_GRADE_LEVEL): ?>
                                                        <li>
                                                            <a class="dropdown-item" href="#" onclick="enrollmentController.graduateStudent(<?php echo $enrollment['enrollment_id']; ?>, '<?php echo $fullNameJs; ?>')">
                                                                <i class="bx bx-medal me-2"></i>Mark as Graduated
                                                            </a>
                                                        </li>
                                                    <?php else: ?>
                                                        <li>
                                                            <span class="dropdown-item disabled" title="Not yet eligible to graduate (must reach <?php echo htmlspecialchars(EnrollmentController::TERMINAL_GRADE_LEVEL); ?>)">
                                                                <i class="bx bx-medal me-2"></i>Mark as Graduated
                                                            </span>
                                                        </li>
                                                    <?php endif; ?>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" onclick="enrollmentController.dropStudent(<?php echo $enrollment['enrollment_id']; ?>, '<?php echo $fullNameJs; ?>')">
                                                            <i class="bx bx-x-circle me-2"></i>Mark as Dropped
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-3">No students found.</td>
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
                                    <a class="page-link" href="?status=<?php echo urlencode($statusFilter); ?>&page=<?php echo $pagination['currentPage'] - 1; ?>">Previous</a>
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
                                    <a class="page-link" href="?status=<?php echo urlencode($statusFilter); ?>&page=1">1</a>
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
                                        <a class="page-link" href="?status=<?php echo urlencode($statusFilter); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
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
                                    <a class="page-link" href="?status=<?php echo urlencode($statusFilter); ?>&page=<?php echo $pagination['totalPages']; ?>"><?php echo $pagination['totalPages']; ?></a>
                                </li>
                            <?php endif; ?>

                            <!-- Next Button -->
                            <?php if($pagination['hasNextPage']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?status=<?php echo urlencode($statusFilter); ?>&page=<?php echo $pagination['currentPage'] + 1; ?>">Next</a>
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
                        <form id="enrollmentForm" method="POST" action="<?= BASE_URL ?>/app/controllers/registrar/EnrollmentController.php">
                            <?php echo Csrf::field(); ?>
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

    <!-- Modal: Edit Enrollment (correct a wrongly-made enrollment in place) -->
    <div class="modal fade" id="editEnrollmentModal" tabindex="-1" aria-labelledby="editEnrollmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editEnrollmentModalLabel">
                        <i class="bx bx-edit"></i> Edit Enrollment
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3" id="editEnrollmentStudentName"></p>
                    <input type="hidden" id="editEnrollmentId">

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label form-label-sm mb-1">School Year <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" id="editEnrollSchoolYear" required>
                                <option value="" disabled>Select School Year</option>
                                <?php foreach ($schoolYears as $sy): ?>
                                    <option value="<?= $sy['id'] ?>"><?= htmlspecialchars($sy['school_year']) ?> (<?= ucfirst($sy['status']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label form-label-sm mb-1">Grade Level <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" id="editEnrollGradeLevel" required>
                                <option value="" disabled>Select Grade Level</option>
                                <?php foreach ($gradeLevels as $grade): ?>
                                    <option value="<?= htmlspecialchars($grade) ?>"><?= htmlspecialchars($grade) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-12">
                            <label class="form-label form-label-sm mb-1">Section <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" id="editEnrollSection" required>
                                <option value="" disabled>Select Section</option>
                            </select>
                            <small class="text-muted d-block mt-1" id="editEnrollSectionCapacityInfo"></small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="enrollmentController.submitEditEnrollment()">
                        <i class="bx bx-check"></i> Save Changes
                    </button>
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

    <!-- Modal: Graduate Student -->
    <div class="modal fade" id="graduateModal" tabindex="-1" aria-labelledby="graduateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="graduateModalLabel">
                        <i class="bx bx-medal"></i> Mark Student as Graduated
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Recording graduation for <strong id="graduateStudentName"></strong></p>
                    <form id="graduateForm">
                        <input type="hidden" id="graduateEnrollmentId" name="enrollment_id">

                        <div class="mb-3">
                            <label class="form-label form-label-sm mb-1">Graduation Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm" id="graduationDate" name="graduation_date" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label form-label-sm mb-1">Honors</label>
                            <input type="text" class="form-control form-control-sm" id="graduateHonors" name="honors" placeholder="e.g. With Honors">
                        </div>

                        <div class="mb-3">
                            <label class="form-label form-label-sm mb-1">Remarks</label>
                            <textarea class="form-control form-control-sm" id="graduateRemarks" name="remarks" rows="3"></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bx bx-check"></i> Confirm Graduation
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/partials/footer.php'; ?>

    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/popper/popper.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/js/bootstrap.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/js/menu.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/main.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/js/toast.js"></script>
    <script src="<?= BASE_URL ?>/public/js/registrar/enrollment.js"></script>
</body>
</html>

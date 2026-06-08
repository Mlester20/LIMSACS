<?php
require_once __DIR__ . '/../../../database/config/config.php';
require_once __DIR__ . '/../../../app/controllers/registrar/EnrollmentController.php';
require_once __DIR__ . '/../../../app/middleware/Auth.php';
require_once __DIR__ . '/../../../app/helpers/message.php';

AuthRole::allowOnly(['registrar']);

try {
    $controller = new EnrollmentController($con);
    $schoolYears = $controller->getSchoolYears();
    $gradeLevels = $controller->getGradeLevels();
} catch (Exception $e) {
    error_log($e->getMessage());
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

    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
    <?php require_once __DIR__ . '/partials/topbar.php'; ?>

    <!-- Main content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Enrollment /</span> Student Enrollment</h4>

        <!-- Display flash messages -->
        <?php showFlash(); ?>

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
                        <tr>
                            <td colspan="8" class="text-center text-muted py-3">Loading enrolled students...</td>
                        </tr>
                    </tbody>
                </table>
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

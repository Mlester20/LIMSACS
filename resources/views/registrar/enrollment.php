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

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Search & Enroll Student</h5>
                    </div>
                    <div class="card-body">
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

            <!-- Sidebar: Recent Enrollments -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Enrollments</h5>
                    </div>
                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Student</th>
                                    <th>School Year</th>
                                    <th>Grade</th>
                                </tr>
                            </thead>
                            <tbody id="recentEnrollmentsTable">
                                <tr>
                                    <td colspan="3" class="text-center text-muted small py-3">No recent enrollments</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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

    <script>
        const enrollmentController = {
            searchStudent: function(term) {
                if (term.length < 2) {
                    document.getElementById('searchResults').innerHTML = '';
                    return;
                }

                $.ajax({
                    url: '../../../app/controllers/registrar/EnrollmentController.php',
                    type: 'POST',
                    data: { search_student: 1, search_term: term },
                    dataType: 'json',
                    success: function(data) {
                        let html = '<div class="list-group mt-2">';
                        if (data.length > 0) {
                            data.forEach(student => {
                                html += `
                                    <button type="button" class="list-group-item list-group-item-action" 
                                            onclick="enrollmentController.selectStudent(${student.id})">
                                        <div class="d-flex w-100 justify-content-between">
                                            <strong>${student.first_name} ${student.last_name}</strong>
                                            <small class="text-muted">LRN: ${student.lrn || 'N/A'}</small>
                                        </div>
                                        <small class="text-muted">${student.gender} | Age: ${student.age || 'N/A'}</small>
                                    </button>
                                `;
                            });
                        } else {
                            html += '<p class="text-muted small p-2">No students found</p>';
                        }
                        html += '</div>';
                        document.getElementById('searchResults').innerHTML = html;
                    },
                    error: function() {
                        document.getElementById('searchResults').innerHTML = '<p class="text-danger small p-2">Search error</p>';
                    }
                });
            },

            selectStudent: function(studentId) {
                $.ajax({
                    url: '../../../app/controllers/registrar/EnrollmentController.php',
                    type: 'POST',
                    data: { get_student_details: 1, student_id: studentId },
                    dataType: 'json',
                    success: function(student) {
                        if (student) {
                            document.getElementById('hiddenStudentId').value = student.id;
                            
                            // Display student profile
                            let profileHtml = `
                                <div>
                                    <div class="mb-2">
                                        <strong>${student.first_name} ${student.middle_name || ''} ${student.last_name}</strong>
                                    </div>
                                    <div class="row g-2 small">
                                        <div class="col-6">
                                            <strong class="text-muted d-block" style="font-size: 0.75rem;">LRN</strong>
                                            ${student.lrn || 'N/A'}
                                        </div>
                                        <div class="col-6">
                                            <strong class="text-muted d-block" style="font-size: 0.75rem;">Gender</strong>
                                            ${student.gender || 'N/A'}
                                        </div>
                                        <div class="col-6">
                                            <strong class="text-muted d-block" style="font-size: 0.75rem;">Birth Date</strong>
                                            ${student.birth_date || 'N/A'}
                                        </div>
                                        <div class="col-6">
                                            <strong class="text-muted d-block" style="font-size: 0.75rem;">Age</strong>
                                            ${student.age || 'N/A'}
                                        </div>
                                    </div>
                                    ${student.enrollment_history && student.enrollment_history.length > 0 ? `
                                        <div class="mt-3">
                                            <strong class="text-muted d-block mb-2" style="font-size: 0.75rem;">Previous Enrollments</strong>
                                            <div class="small">
                                                ${student.enrollment_history.map(eh => `
                                                    <div class="mb-1">${eh.school_year} - ${eh.grade_level} (${eh.section_name || 'N/A'})</div>
                                                `).join('')}
                                            </div>
                                        </div>
                                    ` : ''}
                                </div>
                            `;
                            document.getElementById('studentProfile').innerHTML = profileHtml;
                            
                            // Show sections
                            document.getElementById('studentProfileSection').style.display = 'block';
                            document.getElementById('separator2').style.display = 'block';
                            document.getElementById('enrollmentFormSection').style.display = 'block';
                            document.getElementById('searchResults').innerHTML = '';
                        }
                    },
                    error: function() {
                        alert('Error loading student details');
                    }
                });
            },

            loadSections: function() {
                const schoolYearId = document.getElementById('schoolYear').value;
                const gradeLevel = document.getElementById('gradeLevel').value;

                if (!schoolYearId || !gradeLevel) {
                    document.getElementById('section').innerHTML = '<option value="" disabled selected>Select Section</option>';
                    document.getElementById('sectionCapacityInfo').innerHTML = '';
                    return;
                }

                $.ajax({
                    url: '../../../app/controllers/registrar/EnrollmentController.php',
                    type: 'POST',
                    data: { get_sections: 1, school_year_id: schoolYearId, grade_level: gradeLevel },
                    dataType: 'json',
                    success: function(data) {
                        let html = '<option value="" disabled selected>Select Section</option>';
                        if (data.length > 0) {
                            data.forEach(section => {
                                const available = section.max_students - section.current_enrollment;
                                const isFull = available <= 0 ? 'disabled' : '';
                                html += `<option value="${section.id}" ${isFull}>${section.section_name} (${section.current_enrollment}/${section.max_students})</option>`;
                            });
                        } else {
                            html += '<option disabled>No sections available</option>';
                        }
                        document.getElementById('section').innerHTML = html;
                    }
                });
            },

            updateCapacityInfo: function() {
                const sectionId = document.getElementById('section').value;
                if (sectionId) {
                    const selectedOption = document.querySelector(`#section option[value="${sectionId}"]`);
                    const text = selectedOption.text;
                    document.getElementById('sectionCapacityInfo').innerText = '📋 ' + text;
                }
            }
        };

        // Event listeners
        document.getElementById('searchStudent').addEventListener('keyup', function() {
            enrollmentController.searchStudent(this.value);
        });

        document.getElementById('schoolYear').addEventListener('change', function() {
            enrollmentController.loadSections();
        });

        document.getElementById('gradeLevel').addEventListener('change', function() {
            enrollmentController.loadSections();
        });

        document.getElementById('section').addEventListener('change', function() {
            enrollmentController.updateCapacityInfo();
        });

        function resetForm() {
            document.getElementById('enrollmentForm').reset();
            document.getElementById('searchStudent').value = '';
            document.getElementById('searchResults').innerHTML = '';
            document.getElementById('studentProfileSection').style.display = 'none';
            document.getElementById('separator2').style.display = 'none';
            document.getElementById('enrollmentFormSection').style.display = 'none';
        }
    </script>
</body>
</html>

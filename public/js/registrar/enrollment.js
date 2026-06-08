const enrollmentController = {
    searchStudent: function(term) {
        if (term.length < 2) {
            document.getElementById('searchResults').innerHTML = '';
            return;
        }

        $.ajax({
            url: '../../../app/controllers/registrar/EnrollmentController.php',
            type: 'POST',
            data: {
                search_student: 1,
                search_term: term
            },
            dataType: 'json',
            success: function(data) {
                let html = '<div class="list-group mt-2">';

                if (data.length > 0) {
                    data.forEach(student => {
                        html += `
                            <button
                                type="button"
                                class="list-group-item list-group-item-action"
                                onclick="enrollmentController.selectStudent(${student.id})"
                            >
                                <div class="d-flex w-100 justify-content-between">
                                    <strong>${student.first_name} ${student.last_name}</strong>
                                    <small class="text-muted">
                                        LRN: ${student.lrn || 'N/A'}
                                    </small>
                                </div>

                                <small class="text-muted">
                                    ${student.gender} | Age: ${student.age || 'N/A'}
                                </small>
                            </button>
                        `;
                    });
                } else {
                    html += `
                        <p class="text-muted small p-2">
                            No students found
                        </p>
                    `;
                }

                html += '</div>';
                document.getElementById('searchResults').innerHTML = html;
            },
            error: function() {
                document.getElementById('searchResults').innerHTML = `
                    <p class="text-danger small p-2">
                        Search error
                    </p>
                `;
            }
        });
    },

    selectStudent: function(studentId) {
        $.ajax({
            url: '../../../app/controllers/registrar/EnrollmentController.php',
            type: 'POST',
            data: {
                get_student_details: 1,
                student_id: studentId
            },
            dataType: 'json',
            success: function(student) {
                if (!student) return;

                document.getElementById('hiddenStudentId').value = student.id;

                let profileHtml = `
                    <div>
                        <div class="mb-2">
                            <strong>
                                ${student.first_name}
                                ${student.middle_name || ''}
                                ${student.last_name}
                            </strong>
                        </div>

                        <div class="row g-2 small">
                            <div class="col-6">
                                <strong
                                    class="text-muted d-block"
                                    style="font-size: 0.75rem;"
                                >
                                    LRN
                                </strong>
                                ${student.lrn || 'N/A'}
                            </div>

                            <div class="col-6">
                                <strong
                                    class="text-muted d-block"
                                    style="font-size: 0.75rem;"
                                >
                                    Gender
                                </strong>
                                ${student.gender || 'N/A'}
                            </div>

                            <div class="col-6">
                                <strong
                                    class="text-muted d-block"
                                    style="font-size: 0.75rem;"
                                >
                                    Birth Date
                                </strong>
                                ${student.birth_date || 'N/A'}
                            </div>

                            <div class="col-6">
                                <strong
                                    class="text-muted d-block"
                                    style="font-size: 0.75rem;"
                                >
                                    Age
                                </strong>
                                ${student.age || 'N/A'}
                            </div>
                        </div>

                        ${
                            student.enrollment_history &&
                            student.enrollment_history.length > 0
                                ? `
                                <div class="mt-3">
                                    <strong
                                        class="text-muted d-block mb-2"
                                        style="font-size: 0.75rem;"
                                    >
                                        Previous Enrollments
                                    </strong>

                                    <div class="small">
                                        ${student.enrollment_history
                                            .map(
                                                eh => `
                                                <div class="mb-1">
                                                    ${eh.school_year}
                                                    -
                                                    ${eh.grade_level}
                                                    (${eh.section_name || 'N/A'})
                                                </div>
                                            `
                                            )
                                            .join('')}
                                    </div>
                                </div>
                            `
                                : ''
                        }
                    </div>
                `;

                document.getElementById('studentProfile').innerHTML =
                    profileHtml;

                document.getElementById('studentProfileSection').style.display =
                    'block';

                document.getElementById('separator2').style.display = 'block';

                document.getElementById('enrollmentFormSection').style.display =
                    'block';

                document.getElementById('searchResults').innerHTML = '';
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
            document.getElementById('section').innerHTML =
                '<option value="" disabled selected>Select Section</option>';

            document.getElementById('sectionCapacityInfo').innerHTML = '';
            return;
        }

        $.ajax({
            url: '../../../app/controllers/registrar/EnrollmentController.php',
            type: 'POST',
            data: {
                get_sections: 1,
                school_year_id: schoolYearId,
                grade_level: gradeLevel
            },
            dataType: 'json',
            success: function(data) {
                let html =
                    '<option value="" disabled selected>Select Section</option>';

                if (data.length > 0) {
                    data.forEach(section => {
                        const available =
                            section.max_students -
                            section.current_enrollment;

                        const isFull = available <= 0 ? 'disabled' : '';

                        html += `
                            <option value="${section.id}" ${isFull}>
                                ${section.section_name}
                                (${section.current_enrollment}/${section.max_students})
                            </option>
                        `;
                    });
                } else {
                    html += `
                        <option disabled>
                            No sections available
                        </option>
                    `;
                }

                document.getElementById('section').innerHTML = html;
            }
        });
    },

    updateCapacityInfo: function() {
        const sectionId = document.getElementById('section').value;

        if (!sectionId) return;

        const selectedOption = document.querySelector(
            `#section option[value="${sectionId}"]`
        );

        document.getElementById('sectionCapacityInfo').innerText =
            '📋 ' + selectedOption.text;
    },

    loadEnrolledStudentsTable: function() {
        $.ajax({
            url: '../../../app/controllers/registrar/EnrollmentController.php',
            type: 'POST',
            data: {
                get_all_enrolled_students: 1
            },
            dataType: 'json',
            success: function(data) {
                let html = '';

                if (data && data.length > 0) {
                    data.forEach((enrollment, index) => {
                        const statusBadge = enrollment.enrollment_status === 'Enrolled' 
                            ? '<span class="badge bg-success">Enrolled</span>'
                            : '<span class="badge bg-warning">Transferred</span>';

                        html += `
                            <tr>
                                <td>${index + 1}</td>
                                <td><strong>${enrollment.first_name} ${enrollment.last_name}</strong></td>
                                <td>${enrollment.lrn || 'N/A'}</td>
                                <td>${enrollment.school_year || 'N/A'}</td>
                                <td>${enrollment.grade_level || 'N/A'}</td>
                                <td>${enrollment.section_name || 'N/A'}</td>
                                <td>${statusBadge}</td>
                                <td>
                                    <button 
                                        class="btn btn-sm btn-info" 
                                        onclick="enrollmentController.showEnrollmentHistory(${enrollment.student_id}, '${enrollment.first_name} ${enrollment.last_name}')"
                                        data-bs-toggle="modal"
                                        data-bs-target="#enrollmentHistoryModal"
                                        title="View History"
                                    >
                                        <i class="bx bx-history"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    html = '<tr><td colspan="8" class="text-center text-muted small py-3">No enrolled students</td></tr>';
                }

                document.getElementById('enrolledStudentsBody').innerHTML = html;
            },
            error: function() {
                document.getElementById('enrolledStudentsBody').innerHTML = 
                    '<tr><td colspan="8" class="text-center text-danger small py-3">Error loading students</td></tr>';
            }
        });
    },

    showEnrollmentHistory: function(studentId, studentName) {
        $.ajax({
            url: '../../../app/controllers/registrar/EnrollmentController.php',
            type: 'POST',
            data: {
                get_enrollment_history: 1,
                student_id: studentId
            },
            dataType: 'json',
            success: function(data) {
                let html = `
                    <div class="mb-3">
                        <h6><strong>${studentName}</strong></h6>
                    </div>
                `;

                if (data && data.length > 0) {
                    html += '<div class="timeline">';
                    
                    data.forEach((enrollment, index) => {
                        const statusColor = enrollment.enrollment_status === 'Enrolled' 
                            ? 'success' : 'warning';

                        html += `
                            <div class="mb-3 pb-3" style="border-left: 2px solid #ddd; padding-left: 15px;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>${enrollment.school_year}</strong><br/>
                                        <small class="text-muted">${enrollment.grade_level}</small>
                                    </div>
                                    <span class="badge bg-${statusColor}">${enrollment.enrollment_status}</span>
                                </div>
                                <p class="mb-0 mt-2 small">
                                    <strong>Section:</strong> ${enrollment.section_name || 'N/A'}<br/>
                                    <strong>Enrolled:</strong> ${new Date(enrollment.created_at).toLocaleDateString()}
                                </p>
                            </div>
                        `;
                    });

                    html += '</div>';
                } else {
                    html += '<p class="text-muted">No enrollment history found</p>';
                }

                document.getElementById('enrollmentHistoryContent').innerHTML = html;
            },
            error: function() {
                document.getElementById('enrollmentHistoryContent').innerHTML = 
                    '<p class="text-danger">Error loading enrollment history</p>';
            }
        });
    }
};

// Helper function to reset the enrollment form
function resetForm() {
    document.getElementById('searchStudent').value = '';
    document.getElementById('hiddenStudentId').value = '';
    document.getElementById('studentProfileSection').style.display = 'none';
    document.getElementById('separator2').style.display = 'none';
    document.getElementById('enrollmentFormSection').style.display = 'none';
    document.getElementById('searchResults').innerHTML = '';
    document.getElementById('schoolYear').value = '';
    document.getElementById('gradeLevel').value = '';
    document.getElementById('section').innerHTML = '<option value="" disabled selected>Select Section</option>';
    document.getElementById('sectionCapacityInfo').innerText = '';
}

// Event Listeners

document
    .getElementById('searchStudent')
    .addEventListener('keyup', function() {
        enrollmentController.searchStudent(this.value);
    });

document
    .getElementById('schoolYear')
    .addEventListener('change', function() {
        enrollmentController.loadSections();
    });

document
    .getElementById('gradeLevel')
    .addEventListener('change', function() {
        enrollmentController.loadSections();
    });

document
    .getElementById('section')
    .addEventListener('change', function() {
        enrollmentController.updateCapacityInfo();
    });

// Load enrolled students table on page load
document.addEventListener('DOMContentLoaded', function() {
    enrollmentController.loadEnrolledStudentsTable();
    // Reload table every 5 seconds for real-time updates
    setInterval(enrollmentController.loadEnrolledStudentsTable, 5000);
});

function resetForm() {
    document.getElementById('enrollmentForm').reset();
    document.getElementById('searchStudent').value = '';
    document.getElementById('searchResults').innerHTML = '';

    document.getElementById('studentProfileSection').style.display = 'none';
    document.getElementById('separator2').style.display = 'none';
    document.getElementById('enrollmentFormSection').style.display = 'none';
}
/**
 * Read the CSRF token rendered into the page's <meta name="csrf-token"> tag,
 * needed for AJAX mutations (drop/transfer/graduate) that don't go through a native form.
 */
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

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

    getCurrentStatusFilter: function () {
        const input = document.getElementById('searchInput');
        return (input && input.dataset.statusFilter) || 'Enrolled';
    },

    searchEnrolled: function (keyword, page = 1, status = null) {
        const tbody = document.querySelector('.table tbody');
        if (!tbody) return;

        if (status === null) {
            status = enrollmentController.getCurrentStatusFilter();
        }

        // Show loading state
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-3">Searching...</td></tr>';

        $.ajax({
            url: '../../../app/controllers/registrar/EnrollmentController.php',
            type: 'POST',
            data: {
                search_enrolled: 1,
                keyword: keyword,
                page: page,
                items_per_page: 10,
                status: status
            },
            dataType: 'json',
            success: function (data) {
                const enrollments = data.enrollments || [];
                const pagination  = data.pagination  || {};
                let html = '';

                if (enrollments.length > 0) {
                    enrollments.forEach((enrollment, index) => {
                        const rowNum = (pagination.itemsPerPage * (pagination.currentPage - 1)) + (index + 1);
                        const badgeColor = enrollmentController.getStatusBadgeColor(enrollment.enrollment_status);
                        html += `
                            <tr>
                                <td>${rowNum}</td>
                                <td>${enrollment.first_name} ${enrollment.last_name}</td>
                                <td>${enrollment.lrn || 'N/A'}</td>
                                <td>${enrollment.school_year || 'N/A'}</td>
                                <td>${enrollment.grade_level || 'N/A'}</td>
                                <td>${enrollment.section_name || 'N/A'}</td>
                                <td><span class="badge bg-${badgeColor}">${enrollment.enrollment_status}</span></td>
                                <td>${enrollmentController.renderActionsCell(enrollment)}</td>
                            </tr>
                        `;
                    });
                } else {
                    html = '<tr><td colspan="8" class="text-center text-muted py-3">No results found.</td></tr>';
                }

                tbody.innerHTML = html;

                // Update pagination UI
                enrollmentController.updateSearchPagination(keyword, pagination, status);
            },
            error: function () {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger py-3">Search error. Please try again.</td></tr>';
            }
        });
    },

    updateSearchPagination: function (keyword, pagination, status) {
        const paginationEl = document.querySelector('.pagination');
        if (!paginationEl) return;

        status = status || enrollmentController.getCurrentStatusFilter();

        if (!keyword || keyword.trim() === '') {
            // Reload the page to restore PHP pagination when search is cleared
            window.location.href = '?status=' + encodeURIComponent(status) + '&page=1';
            return;
        }

        let html = '';

        // Previous
        html += pagination.hasPrevPage
            ? `<li class="page-item"><a class="page-link" href="#" onclick="enrollmentController.searchEnrolled('${keyword}', ${pagination.currentPage - 1}, '${status}'); return false;">Previous</a></li>`
            : `<li class="page-item disabled"><span class="page-link">Previous</span></li>`;

        // Page numbers
        const start = Math.max(1, pagination.currentPage - 2);
        const end   = Math.min(pagination.totalPages, pagination.currentPage + 2);

        for (let i = start; i <= end; i++) {
            html += i === pagination.currentPage
                ? `<li class="page-item active"><span class="page-link">${i}</span></li>`
                : `<li class="page-item"><a class="page-link" href="#" onclick="enrollmentController.searchEnrolled('${keyword}', ${i}, '${status}'); return false;">${i}</a></li>`;
        }

        // Next
        html += pagination.hasNextPage
            ? `<li class="page-item"><a class="page-link" href="#" onclick="enrollmentController.searchEnrolled('${keyword}', ${pagination.currentPage + 1}, '${status}'); return false;">Next</a></li>`
            : `<li class="page-item disabled"><span class="page-link">Next</span></li>`;

        paginationEl.innerHTML = html;
    },


    getStatusBadgeColor: function(status) {
        switch (status) {
            case 'Enrolled': return 'success';
            case 'Graduated': return 'primary';
            case 'Transferred': return 'warning';
            case 'Dropped': return 'danger';
            default: return 'secondary';
        }
    },

    getTerminalGradeLevel: function () {
        const tbody = document.getElementById('enrolledStudentsBody');
        return (tbody && tbody.dataset.terminalGrade) || 'Grade 6';
    },

    renderActionsCell: function(enrollment) {
        const fullName = `${enrollment.first_name} ${enrollment.last_name}`;

        const historyBtn = `
            <button class="btn btn-sm btn-info" title="View History"
                data-bs-toggle="modal"
                data-bs-target="#enrollmentHistoryModal"
                onclick="enrollmentController.showEnrollmentHistory(${enrollment.student_id}, '${fullName}')">
                <i class="bx bx-history"></i>
            </button>
        `;

        if (enrollment.enrollment_status !== 'Enrolled') {
            const badgeColor = enrollmentController.getStatusBadgeColor(enrollment.enrollment_status);
            return historyBtn + `<span class="badge bg-${badgeColor}">${enrollment.enrollment_status}</span>`;
        }

        const terminalGrade = enrollmentController.getTerminalGradeLevel();
        const graduateBtn = enrollment.grade_level === terminalGrade
            ? `
                <button class="btn btn-sm btn-primary" title="Mark as Graduated"
                    onclick="enrollmentController.graduateStudent(${enrollment.enrollment_id}, '${fullName}')">
                    <i class="bx bx-medal"></i>
                </button>
            `
            : `
                <button class="btn btn-sm btn-outline-secondary" disabled
                    title="Not yet eligible to graduate (must reach ${terminalGrade})">
                    <i class="bx bx-medal"></i>
                </button>
            `;

        return historyBtn + `
            <button class="btn btn-sm btn-danger" title="Mark as Dropped"
                onclick="enrollmentController.dropStudent(${enrollment.enrollment_id}, '${fullName}')">
                <i class="bx bx-x-circle"></i>
            </button>
            <button class="btn btn-sm btn-warning" title="Mark as Transferred"
                onclick="enrollmentController.transferStudent(${enrollment.enrollment_id}, '${fullName}')">
                <i class="bx bx-transfer"></i>
            </button>
            ${graduateBtn}
        `;
    },

    dropStudent: function(enrollmentId, studentName) {
        if (!confirm(`Are you sure you want to mark ${studentName} as Dropped?`)) {
            return;
        }

        $.ajax({
            url: '../../../app/controllers/registrar/EnrollmentController.php',
            type: 'POST',
            data: {
                update_status: 1,
                enrollment_id: enrollmentId,
                new_status: 'Dropped',
                csrf_token: getCsrfToken()
            },
            dataType: 'json',
            success: function(response) {
                alert(response.message);
                if (response.success) {
                    window.location.reload();
                }
            },
            error: function() {
                alert('Error updating student status.');
            }
        });
    },

    transferStudent: function(enrollmentId, studentName) {
        if (!confirm(`Are you sure you want to mark ${studentName} as Transferred?`)) {
            return;
        }

        $.ajax({
            url: '../../../app/controllers/registrar/EnrollmentController.php',
            type: 'POST',
            data: {
                update_status: 1,
                enrollment_id: enrollmentId,
                new_status: 'Transferred',
                csrf_token: getCsrfToken()
            },
            dataType: 'json',
            success: function(response) {
                alert(response.message);
                if (response.success) {
                    window.location.reload();
                }
            },
            error: function() {
                alert('Error updating student status.');
            }
        });
    },

    graduateStudent: function(enrollmentId, studentName) {
        document.getElementById('graduateForm').reset();
        document.getElementById('graduateEnrollmentId').value = enrollmentId;
        document.getElementById('graduateStudentName').textContent = studentName;

        const modal = new bootstrap.Modal(document.getElementById('graduateModal'));
        modal.show();
    },

    submitGraduation: function() {
        const enrollmentId = document.getElementById('graduateEnrollmentId').value;
        const graduationDate = document.getElementById('graduationDate').value;
        const honors = document.getElementById('graduateHonors').value;
        const remarks = document.getElementById('graduateRemarks').value;

        if (!graduationDate) {
            alert('Graduation date is required.');
            return;
        }

        $.ajax({
            url: '../../../app/controllers/registrar/EnrollmentController.php',
            type: 'POST',
            data: {
                update_status: 1,
                enrollment_id: enrollmentId,
                new_status: 'Graduated',
                graduation_date: graduationDate,
                honors: honors,
                remarks: remarks,
                csrf_token: getCsrfToken()
            },
            dataType: 'json',
            success: function(response) {
                alert(response.message);
                if (response.success) {
                    bootstrap.Modal.getInstance(document.getElementById('graduateModal')).hide();
                    window.location.reload();
                }
            },
            error: function() {
                alert('Error recording graduation.');
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
                        const statusColor = enrollmentController.getStatusBadgeColor(enrollment.enrollment_status);

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

// ===== Helper: Reset enrollment form =====
function resetForm() {
    document.getElementById('enrollmentForm').reset();
    document.getElementById('searchStudent').value = '';
    document.getElementById('hiddenStudentId').value = '';
    document.getElementById('searchResults').innerHTML = '';
    document.getElementById('studentProfileSection').style.display = 'none';
    document.getElementById('separator2').style.display = 'none';
    document.getElementById('enrollmentFormSection').style.display = 'none';
    document.getElementById('schoolYear').value = '';
    document.getElementById('gradeLevel').value = '';
    document.getElementById('section').innerHTML = '<option value="" disabled selected>Select Section</option>';
    document.getElementById('sectionCapacityInfo').innerText = '';
}


// ===== All Event Listeners inside DOMContentLoaded =====
document.addEventListener('DOMContentLoaded', function () {

    // Modal: search student by LRN or name
    document.getElementById('searchStudent')
        .addEventListener('keyup', function () {
            enrollmentController.searchStudent(this.value);
        });

    document.getElementById('schoolYear')
        .addEventListener('change', function () {
            enrollmentController.loadSections();
        });

    document.getElementById('gradeLevel')
        .addEventListener('change', function () {
            enrollmentController.loadSections();
        });

    document.getElementById('section')
        .addEventListener('change', function () {
            enrollmentController.updateCapacityInfo();
        });

    // Search enrolled students — server-side WHERE LIKE
    let searchTimer = null;
    document.getElementById('searchInput').addEventListener('keyup', function () {
        const keyword = this.value.trim();
        clearTimeout(searchTimer);

        if (keyword === '') {
            // Restore PHP-rendered table by reloading page
            window.location.href = '?status=' + encodeURIComponent(enrollmentController.getCurrentStatusFilter()) + '&page=1';
            return;
        }

        // Debounce: wait 400ms after user stops typing before firing AJAX
        searchTimer = setTimeout(function () {
            enrollmentController.searchEnrolled(keyword, 1);
        }, 400);
    });

    // Graduate modal: submit graduation details via AJAX
    document.getElementById('graduateForm')
        .addEventListener('submit', function (e) {
            e.preventDefault();
            enrollmentController.submitGraduation();
        });

});
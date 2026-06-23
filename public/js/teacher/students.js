/**
 * Read the CSRF token rendered into the page's <meta name="csrf-token"> tag,
 * needed for AJAX mutations (edit/drop/transfer) and dynamically built forms.
 */
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

function populateStudentModal(student) {
    const studentId = student.id;

    $.ajax({
        url: '../../../app/controllers/teacher/StudentController.php',
        type: 'POST',
        data: {
            action: 'get_student_profile',
            student_id: studentId
        },
        dataType: 'json',
        success: function(response) {
            const studentData = response.student || student;
            const academicHistory = response.academic_history || [];
            const parentGuardians = response.parent_guardians || {};
            const studentDocuments = response.student_documents || [];
            const documentTypes = response.document_types || [];

            // Personal Information
            document.getElementById('modalLrn').textContent = studentData.lrn || '-';
            document.getElementById('modalFirstName').textContent = studentData.first_name || '-';
            document.getElementById('modalMiddleName').textContent = studentData.middle_name || '-';
            document.getElementById('modalLastName').textContent = studentData.last_name || '-';
            document.getElementById('modalGender').textContent = studentData.gender || '-';

            // Birth Information
            document.getElementById('modalBirthDate').textContent = studentData.birth_date || '-';
            document.getElementById('modalAge').textContent = studentData.age || '-';
            document.getElementById('modalPlaceOfBirth').textContent = studentData.place_of_birth || '-';

            // Contact Information
            document.getElementById('modalContactNumber').textContent = studentData.contact_number || '-';
            document.getElementById('modalAddress').textContent = studentData.address || '-';

            // Other Information
            document.getElementById('modalReligion').textContent = studentData.religion || '-';

            // Status Badge
            const statusBadge = document.getElementById('modalStatusBadge');
            const status = student.enrollment_status || 'Active';

            statusBadge.textContent = status;

            switch (status) {
                case 'Enrolled':
                    statusBadge.className = 'badge bg-success';
                    break;
                case 'Dropped':
                    statusBadge.className = 'badge bg-danger';
                    break;
                case 'Transferred':
                    statusBadge.className = 'badge bg-warning';
                    break;
                case 'Graduated':
                    statusBadge.className = 'badge bg-primary';
                    break;
                default:
                    statusBadge.className = 'badge bg-secondary';
                    break;
            }

            renderAcademicHistory(academicHistory);
            renderParentGuardians(parentGuardians);
            renderDocumentChecklist(studentDocuments, documentTypes);
        },
        error: function(error) {
            console.error('Error fetching student profile:', error);
            document.getElementById('modalLrn').textContent = student.lrn || '-';
            document.getElementById('modalFirstName').textContent = student.first_name || '-';
            document.getElementById('modalMiddleName').textContent = student.middle_name || '-';
            document.getElementById('modalLastName').textContent = student.last_name || '-';
        }
    });
}

/**
 * Render academic history table
 * @param {Array} academicHistory - Array of enrollment records
 */
function renderAcademicHistory(academicHistory) {
    const tableBody = document.getElementById('academicHistoryBody');
    if (!tableBody) return;

    if (!academicHistory || academicHistory.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No academic history found</td></tr>';
        return;
    }

    let html = '';
    academicHistory.forEach(record => {
        html += `
            <tr>
                <td>${record.school_year || 'N/A'}</td>
                <td>${record.grade_level || 'N/A'} - ${record.section_name || 'N/A'}</td>
                <td>${record.adviser_name || 'N/A'}</td>
                <td>
                    <span class="badge bg-${record.enrollment_status === 'Enrolled' ? 'success' : 'secondary'}">
                        ${record.enrollment_status || 'N/A'}
                    </span>
                </td>
            </tr>
        `;
    });

    tableBody.innerHTML = html;
}

/**
 * Render parent/guardians information (read-only; managed on the dedicated Parents/Guardians page)
 * @param {Object} parentGuardians - Parent/Guardian data
 */
function renderParentGuardians(parentGuardians) {
    const container = document.getElementById('parentGuardiansInfo');
    if (!container) return;

    if (!parentGuardians || Object.keys(parentGuardians).length === 0) {
        container.innerHTML = '<p class="text-muted text-center">No parent/guardian information found</p>';
        return;
    }

    let html = '<div class="row">';

    if (parentGuardians.father_name) {
        html += `
            <div class="col-md-6 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title text-primary">Father</h6>
                        <p class="mb-1"><strong>Name:</strong> ${parentGuardians.father_name || 'N/A'}</p>
                        <p class="mb-1"><strong>Occupation:</strong> ${parentGuardians.father_occupation || 'N/A'}</p>
                        <p class="mb-0"><strong>Contact:</strong> ${parentGuardians.father_contact || 'N/A'}</p>
                    </div>
                </div>
            </div>
        `;
    }

    if (parentGuardians.mother_name) {
        html += `
            <div class="col-md-6 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title text-success">Mother</h6>
                        <p class="mb-1"><strong>Name:</strong> ${parentGuardians.mother_name || 'N/A'}</p>
                        <p class="mb-1"><strong>Occupation:</strong> ${parentGuardians.mother_occupation || 'N/A'}</p>
                        <p class="mb-0"><strong>Contact:</strong> ${parentGuardians.mother_contact || 'N/A'}</p>
                    </div>
                </div>
            </div>
        `;
    }

    if (parentGuardians.guardian_name) {
        html += `
            <div class="col-md-6 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title text-warning">Guardian</h6>
                        <p class="mb-1"><strong>Name:</strong> ${parentGuardians.guardian_name || 'N/A'}</p>
                        <p class="mb-1"><strong>Relationship:</strong> ${parentGuardians.guardian_relationship || 'N/A'}</p>
                        <p class="mb-0"><strong>Contact:</strong> ${parentGuardians.guardian_contact || 'N/A'}</p>
                    </div>
                </div>
            </div>
        `;
    }

    html += '</div>';
    container.innerHTML = html;
}

/**
 * Resolve the app's base URL (project root), regardless of how deep the
 * current page is nested under /resources/views/...
 */
function getAppBaseUrl() {
    const path = window.location.pathname;
    const idx = path.lastIndexOf('/resources');
    return idx !== -1 ? path.substring(0, idx) : '';
}

/**
 * Resolve a stored relative file path into an absolute URL from the project root.
 * @param {string} filePath
 */
function resolveDocumentUrl(filePath) {
    if (!filePath) return '';
    return getAppBaseUrl() + '/' + filePath.replace(/^\/+/, '');
}

/**
 * Open a document for viewing/downloading.
 * @param {string} filePath - relative file path stored in the DB
 * @param {string} label - human-readable name for the document
 */
function openDocumentViewer(filePath, label) {
    const url = resolveDocumentUrl(filePath);
    if (!url) return;

    const ext = (filePath.split('.').pop() || '').toLowerCase();

    if (ext === 'pdf') {
        const frame = document.getElementById('documentViewerFrame');
        const title = document.getElementById('documentViewerLabel');
        const openNewTab = document.getElementById('documentViewerOpenNewTab');

        if (frame) frame.src = url;
        if (title) title.textContent = label || 'Document Viewer';
        if (openNewTab) openNewTab.href = url;

        const modalEl = document.getElementById('documentViewerModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    } else {
        const link = document.createElement('a');
        link.href = url;
        link.download = filePath.split('/').pop() || '';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('documentViewerModal');
    if (!modalEl) return;
    modalEl.addEventListener('hidden.bs.modal', function () {
        const frame = document.getElementById('documentViewerFrame');
        if (frame) frame.src = 'about:blank';
    });
});

/**
 * Render the documents checklist tab
 * @param {Array} studentDocs - Documents submitted by the student
 * @param {Array} docTypes - All available document types
 */
function renderDocumentChecklist(studentDocs, docTypes) {
    const tableBody = document.getElementById('documentChecklistBody');
    const summary = document.getElementById('documentChecklistSummary');
    if (!tableBody) return;

    if (!docTypes || docTypes.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No document types configured</td></tr>';
        if (summary) summary.textContent = '';
        return;
    }

    const docsByTypeId = new Map();
    (studentDocs || []).forEach(doc => {
        docsByTypeId.set(String(doc.document_type_id), doc);
    });

    const statusBadgeClass = {
        Submitted: 'bg-info',
        Verified: 'bg-success',
        Rejected: 'bg-danger',
        Pending: 'bg-warning'
    };

    let submittedCount = 0;
    let html = '';

    docTypes.forEach(docType => {
        const doc = docsByTypeId.get(String(docType.id));

        if (doc) {
            submittedCount++;
            const badgeClass = statusBadgeClass[doc.status] || 'bg-secondary';
            const ext = (doc.file_path || '').split('.').pop().toLowerCase();
            const actionLabel = ext === 'pdf' ? 'View' : 'Download';
            html += `
                <tr>
                    <td>${escapeHtml(docType.document_name)}</td>
                    <td><span class="badge ${badgeClass}">${escapeHtml(doc.status || 'N/A')}</span></td>
                    <td>${escapeHtml(doc.remarks || '-')}</td>
                    <td>
                        ${doc.file_path ? `<button type="button" class="btn btn-sm btn-outline-primary" onclick='openDocumentViewer(${JSON.stringify(doc.file_path)}, ${JSON.stringify(docType.document_name)})'>${actionLabel}</button>` : '-'}
                    </td>
                </tr>
            `;
        } else {
            html += `
                <tr>
                    <td>${escapeHtml(docType.document_name)}</td>
                    <td><span class="badge bg-danger">✗ Missing</span></td>
                    <td></td>
                    <td></td>
                </tr>
            `;
        }
    });

    tableBody.innerHTML = html;

    if (summary) {
        summary.textContent = `${submittedCount} of ${docTypes.length} documents submitted`;
    }
}

/**
 * Edit a student - populates the edit modal with student data
 * @param {Object} student - Student data object
 */
function editStudent(student) {
    document.getElementById('editStudentId').value = student.id || '';

    document.getElementById('editLrn').value = student.lrn || '';

    document.getElementById('editFirstName').value = student.first_name || '';
    document.getElementById('editMiddleName').value = student.middle_name || '';
    document.getElementById('editLastName').value = student.last_name || '';
    document.getElementById('editSuffix').value = student.suffix || '';

    document.getElementById('editGender').value = student.gender || '';

    document.getElementById('editBirthDate').value = student.birth_date || '';
    document.getElementById('editAge').value = student.age || '';
    document.getElementById('editPlaceOfBirth').value = student.place_of_birth || '';

    document.getElementById('editNationality').value = student.nationality || '';
    document.getElementById('editReligion').value = student.religion || '';

    document.getElementById('editContactNumber').value = student.contact_number || '';
    document.getElementById('editAddress').value = student.address || '';
}

/**
 * Escape HTML special characters for safe display
 * @param {string} text - Text to escape
 */
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

const studentsController = {
    dropStudent: function(enrollmentId, studentName) {
        if (!confirm(`Are you sure you want to mark ${studentName} as Dropped?`)) {
            return;
        }

        $.ajax({
            url: '../../../app/controllers/teacher/StudentController.php',
            type: 'POST',
            data: {
                action: 'update_status',
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
            url: '../../../app/controllers/teacher/StudentController.php',
            type: 'POST',
            data: {
                action: 'update_status',
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
    }
};

// Add hover effect to table rows
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.table tbody tr').forEach(row => {
        row.addEventListener('mouseover', function () {
            this.style.backgroundColor = '#f5f5f5';
            this.style.cursor = 'pointer';
        });

        row.addEventListener('mouseout', function () {
            this.style.backgroundColor = '';
        });
    });
});

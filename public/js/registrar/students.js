function populateStudentModal(student) {
    const studentId = student.id;
    
    $.ajax({
        url: '../../../app/controllers/registrar/StudentsController.php',
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
            const status = studentData.student_status || 'Active';

            statusBadge.textContent = status;

            // Status color mapping
            switch (status) {
                case 'Enrolled':
                    statusBadge.className = 'badge bg-success';
                    break;

                case 'Inactive':
                    statusBadge.className = 'badge bg-secondary';
                    break;

                case 'Transferred':
                    statusBadge.className = 'badge bg-warning';
                    break;

                case 'Graduated':
                    statusBadge.className = 'badge bg-info';
                    break;

                default:
                    statusBadge.className = 'badge bg-primary';
                    break;
            }

            // Render Academic History
            renderAcademicHistory(academicHistory);

            // Render Parent/Guardians Information
            renderParentGuardians(parentGuardians);

            // Render Documents Checklist
            renderDocumentChecklist(studentDocuments, documentTypes);
        },
        error: function(error) {
            console.error('Error fetching student profile:', error);
            // Fallback to basic student data display
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
 * Render parent/guardians information
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

    // Father Information
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

    // Mother Information
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

    // Guardian Information
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
 * e.g. '/LIMSACS/resources/views/registrar/x.php' -> '/LIMSACS'
 */
function getAppBaseUrl() {
    const path = window.location.pathname;
    const idx = path.lastIndexOf('/resources');
    return idx !== -1 ? path.substring(0, idx) : '';
}

/**
 * Read the CSRF token rendered into the page's <meta name="csrf-token"> tag,
 * needed for forms/requests built dynamically in JS (e.g. search result rows).
 */
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

/**
 * Resolve a stored relative file path (e.g. 'storage/student_documents/doc_x.pdf')
 * into an absolute URL from the project root.
 * @param {string} filePath
 */
function resolveDocumentUrl(filePath) {
    if (!filePath) return '';
    return getAppBaseUrl() + '/' + filePath.replace(/^\/+/, '');
}

/**
 * Open a document for viewing/downloading.
 * PDFs are shown inline in a viewer modal; other file types (e.g. .docx) are
 * downloaded directly since browsers cannot render them inline.
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

// Reset the viewer iframe when the modal closes so playback/loading stops
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
    // Populate hidden student ID
    document.getElementById('editStudentId').value = student.id || '';

    // Student Identity
    document.getElementById('editLrn').value = student.lrn || '';

    // Personal Information
    document.getElementById('editFirstName').value = student.first_name || '';
    document.getElementById('editMiddleName').value = student.middle_name || '';
    document.getElementById('editLastName').value = student.last_name || '';
    document.getElementById('editSuffix').value = student.suffix || '';

    // Gender
    document.getElementById('editGender').value = student.gender || '';

    // Birth Information
    document.getElementById('editBirthDate').value = student.birth_date || '';
    document.getElementById('editAge').value = student.age || '';
    document.getElementById('editPlaceOfBirth').value = student.place_of_birth || '';

    // Demographics
    document.getElementById('editNationality').value = student.nationality || '';
    document.getElementById('editReligion').value = student.religion || '';

    // Contact & Address
    document.getElementById('editContactNumber').value = student.contact_number || '';
    document.getElementById('editAddress').value = student.address || '';
}

// Add hover effect to table rows
document.querySelectorAll('.table tbody tr').forEach(row => {
    row.addEventListener('mouseover', function () {
        this.style.backgroundColor = '#f5f5f5';
        this.style.cursor = 'pointer';
    });

    row.addEventListener('mouseout', function () {
        this.style.backgroundColor = '';
    });
});

//auto calculated age
document.getElementById('enrollBirthDate').addEventListener('change', function () {
    const birthDate = new Date(this.value);
    const today = new Date();

    if (!this.value || birthDate > today) {
        document.getElementById('ageInput').value = '';
        return;
    }

    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();

    // Adjust if birthday hasn't occurred yet this year
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }

    document.getElementById('ageInput').value = age;
});

function deleteStudent(id) {
    if (!confirm('Are you sure you want to delete this student? This action cannot be undone.')) return;

    fetch(`../../../app/controllers/registrar/EnrollStudentController.php?action=delete&id=${id}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${id}`
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Student deleted successfully.');
                location.reload();
            } else {
                alert(data.message || 'Failed to delete student.');
            }
        })
        .catch(err => {
            console.error('deleteStudent error:', err);
            alert('An error occurred while deleting the student.');
        });
}

// ============== create rest api to create search student function ==================== //

let searchTimeout;

/**
 * Debounce function to limit search requests
 * @param {Function} func - Function to debound
 * @param {number} delay - Delay in milliseconds
 */

function debounce(func, delay){
    return function(...args){
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => func.apply(this, args), delay);
    }
}

/**
 * Perform student search via REST API
 * @param {string} keyword - search keyword
 */
async function searchStudents(keyword){
    const tableBody = document.querySelector('table tbody');
    const paginationContainer = document.querySelector('.card-footer');

    //validate keyword
    if(!keyword || keyword.trim().length < 2){
        //reset to show all students if keyword is empty or too short
        if(keyword === ''){
            location.reload();
        }
        return;
    }

    try{
        //disable pagination during search
        if(paginationContainer){
            paginationContainer.style.display = 'none';
        }

        //show loading state
        if(tableBody){
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center"><span class="spinner-border spinner-border-sm me-2"></span>Searching...</td></tr>';
        }

        //build  api url with absolute path
        const apiUrl = getAppBaseUrl() + '/app/api/registrar/search-students.php?q=' + encodeURIComponent(keyword.trim());

        const response = await fetch(apiUrl, {
            method: 'GET', 
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });

        if(!response.ok){
            throw new Error(`API error: ${response.status}`);
        }

        const data = await response.json();

        if(!data.success){
            throw new Error(data.message || 'Search failed');
        }

        renderSearchResults(data.data || []);

    }catch(error){
       console.error('Search error:', error);
       //show error message
       if(tableBody){
          tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error searching students. Please try again.</td></tr>';
       } 
    }
}

/**
 * Render search results in the table
 * @param {Array} results - Search results from API
 */
function renderSearchResults(results){
    const tableBody = document.querySelector('table tbody');
    const paginationContainer = document.querySelector('.card-footer');

    if(!tableBody) return;

    //clear existing content
    tableBody.innerHTML = '';

    if(results.length === 0){
        tableBody.innerHTML = '<tr><td colspan="6" class="text-center">No students found matching your search.</td></tr>';
        if(paginationContainer){
            paginationContainer.style.display = 'none';
        }
        return;
    }

    const rows = results.map((student, index) => {
        const lrn = student.lrn || 'N/A';
        const studentName = `${student.first_name || ''} ${student.middle_name || ''} ${student.last_name || ''}`.trim();
        const gender = student.gender
        const age = student.age
        // const enrollmentStatus = student.enrollment_status || 'N/A';

        return `
            <tr 
                style="cursor: pointer;" 
                data-bs-toggle="modal" 
                data-bs-target="#studentDetailsModal"
                onclick='populateStudentModal(${JSON.stringify(student)})'
            >
                <td>${escapeHtml(index + 1)}</td>
                <td>${escapeHtml(lrn)}</td>
                <td>${escapeHtml(studentName)}</td>
                <td>${escapeHtml(gender)}</td>
                <td>${escapeHtml(age)}</td>
                <td>
                    <button 
                        class="btn btn-sm btn-primary me-1"
                        title="Edit Student"
                        data-bs-toggle="modal"
                        data-bs-target="#editStudentModal"
                        onclick='event.stopPropagation(); editStudent(${JSON.stringify(student)})'
                    >
                        Edit
                    </button>
                    <form method="POST" action="../../../app/controllers/registrar/StudentsController.php" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="${getCsrfToken()}">
                        <input type="hidden" name="student_id" value="${student.id}">
                        <button
                            type="submit"
                            class="btn btn-sm btn-danger"
                            onclick="return confirm('Are you sure you want to delete this student? This action cannot be undone.');"
                            name="delete_student"
                        >
                            Delete
                        </button>
                    </form>
                </td>
            </tr>`;
    }).join('');

    //insert rows into table
    tableBody.innerHTML = rows;

    //hide pagination during search
    if(paginationContainer){
        paginationContainer.style.display = 'none';
    }
}

/**
 * Escape HTML special characters for safe display
 * @param {string} text - Text to escape
 */

function escapeHtml(text){
    if(!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

/**
 * Initialize search functionality
 */
function initializeSearch(){
    const searchInput = document.getElementById('searchInput');

    if(!searchInput) return; 

    const debouncedSearch = debounce((e) => {
        const keyword = e.target.value.trim();
        searchStudents(keyword);
    
    }, 300);

    searchInput.addEventListener('input', debouncedSearch);
}

document.addEventListener('DOMContentLoaded', initializeSearch);
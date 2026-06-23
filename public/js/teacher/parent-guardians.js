/**
 * Read the CSRF token rendered into the page's <meta name="csrf-token"> tag,
 * needed for forms built dynamically in JS.
 */
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

function editParentGuardian(id, studentId, studentName, fatherName, fatherOccupation, fatherContact, motherName, motherOccupation, motherContact, guardianName, guardianRelationship, guardianContact) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_student_id').value = studentId || '';
    document.getElementById('editStudentNameDisplay').value = studentName || '';
    document.getElementById('editFatherName').value = fatherName || '';
    document.getElementById('editFatherOccupation').value = fatherOccupation || '';
    document.getElementById('editFatherContact').value = fatherContact || '';
    document.getElementById('editMotherName').value = motherName || '';
    document.getElementById('editMotherOccupation').value = motherOccupation || '';
    document.getElementById('editMotherContact').value = motherContact || '';
    document.getElementById('editGuardianName').value = guardianName || '';
    document.getElementById('editGuardianRelationship').value = guardianRelationship || '';
    document.getElementById('editGuardianContact').value = guardianContact || '';
}

function viewParentGuardian(studentName, fatherName, fatherOccupation, fatherContact, motherName, motherOccupation, motherContact, guardianName, guardianRelationship, guardianContact) {
    document.getElementById('viewStudentName').value = studentName || '';
    document.getElementById('viewFatherName').value = fatherName || '';
    document.getElementById('viewFatherOccupation').value = fatherOccupation || '';
    document.getElementById('viewFatherContact').value = fatherContact || '';
    document.getElementById('viewMotherName').value = motherName || '';
    document.getElementById('viewMotherOccupation').value = motherOccupation || '';
    document.getElementById('viewMotherContact').value = motherContact || '';
    document.getElementById('viewGuardianName').value = guardianName || '';
    document.getElementById('viewGuardianRelationship').value = guardianRelationship || '';
    document.getElementById('viewGuardianContact').value = guardianContact || '';
}

// ========================================
// Search Functionality (scoped to this teacher's students)
// ========================================

let searchTimeout;

/**
 * Debounce function to limit search requests
 * @param {Function} func - Function to debounce
 * @param {number} delay - Delay in milliseconds
 */
function debounce(func, delay) {
    return function (...args) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => func.apply(this, args), delay);
    };
}

/**
 * Perform parent/guardian search, scoped to this teacher's students
 * @param {string} keyword - Search keyword
 */
function searchGuardians(keyword) {
    const tableBody = document.querySelector('table tbody');
    const paginationContainer = document.querySelector('.card-footer');

    if (!keyword || keyword.trim().length < 2) {
        if (keyword === '') {
            location.reload();
        }
        return;
    }

    if (paginationContainer) {
        paginationContainer.style.display = 'none';
    }

    if (tableBody) {
        tableBody.innerHTML = '<tr><td colspan="5" class="text-center"><span class="spinner-border spinner-border-sm me-2"></span>Searching...</td></tr>';
    }

    $.ajax({
        url: '../../../app/controllers/teacher/ParentGuardiansController.php',
        type: 'POST',
        data: {
            search_guardians: 1,
            keyword: keyword.trim()
        },
        dataType: 'json',
        success: function (data) {
            renderSearchResults(data || []);
        },
        error: function () {
            if (tableBody) {
                tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error searching guardians. Please try again.</td></tr>';
            }
        }
    });
}

/**
 * Render search results in the table
 * @param {Array} results - Search results
 */
function renderSearchResults(results) {
    const tableBody = document.querySelector('table tbody');
    const paginationContainer = document.querySelector('.card-footer');

    if (!tableBody) return;

    tableBody.innerHTML = '';

    if (results.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="5" class="text-center">No guardians found matching your search.</td></tr>';
        if (paginationContainer) {
            paginationContainer.style.display = 'none';
        }
        return;
    }

    const rows = results.map((guardian, index) => {
        const studentName = `${guardian.student_first_name || ''} ${guardian.student_last_name || ''}`.trim();

        return `
            <tr>
                <td>${escapeHtml(index + 1)}</td>
                <td>${escapeHtml(studentName)}</td>
                <td>${escapeHtml(guardian.father_name)}</td>
                <td>${escapeHtml(guardian.mother_name)}</td>
                <td>
                    <button
                        class="btn btn-sm btn-info"
                        title="View"
                        data-bs-toggle="modal"
                        data-bs-target="#viewGuardianModal"
                        onclick='viewParentGuardian(
                            ${JSON.stringify(studentName)},
                            ${JSON.stringify(guardian.father_name || "")},
                            ${JSON.stringify(guardian.father_occupation || "")},
                            ${JSON.stringify(guardian.father_contact || "")},
                            ${JSON.stringify(guardian.mother_name || "")},
                            ${JSON.stringify(guardian.mother_occupation || "")},
                            ${JSON.stringify(guardian.mother_contact || "")},
                            ${JSON.stringify(guardian.guardian_name || "")},
                            ${JSON.stringify(guardian.guardian_relationship || "")},
                            ${JSON.stringify(guardian.guardian_contact || "")}
                        )'
                    >
                        <i class="bx bx-eye"></i>
                    </button>
                    <button
                        class="btn btn-sm btn-primary"
                        title="Edit"
                        data-bs-toggle="modal"
                        data-bs-target="#editGuardianModal"
                        onclick='editParentGuardian(
                            ${guardian.id},
                            ${guardian.student_id},
                            ${JSON.stringify(studentName)},
                            ${JSON.stringify(guardian.father_name || "")},
                            ${JSON.stringify(guardian.father_occupation || "")},
                            ${JSON.stringify(guardian.father_contact || "")},
                            ${JSON.stringify(guardian.mother_name || "")},
                            ${JSON.stringify(guardian.mother_occupation || "")},
                            ${JSON.stringify(guardian.mother_contact || "")},
                            ${JSON.stringify(guardian.guardian_name || "")},
                            ${JSON.stringify(guardian.guardian_relationship || "")},
                            ${JSON.stringify(guardian.guardian_contact || "")}
                        )'
                    >
                        <i class="bx bx-edit-alt"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join('');

    tableBody.innerHTML = rows;

    if (paginationContainer) {
        paginationContainer.style.display = 'none';
    }
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

/**
 * Initialize main table search
 */
function initializeSearch() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;

    const debouncedSearch = debounce((e) => {
        const keyword = e.target.value.trim();
        searchGuardians(keyword);
    }, 300);

    searchInput.addEventListener('input', debouncedSearch);
}

// ========================================
// Add Guardian: Student Search (scoped to this teacher's students without a guardian)
// ========================================

let studentSearchTimer = null;

/**
 * Search this teacher's students who don't yet have a guardian record
 * @param {string} keyword
 */
function searchAvailableStudents(keyword) {
    const resultsContainer = document.getElementById('studentSearchResults');
    const warning = document.getElementById('duplicateWarning');

    if (!keyword || keyword.trim().length < 2) {
        resultsContainer.style.display = 'none';
        resultsContainer.innerHTML = '';
        return;
    }

    $.ajax({
        url: '../../../app/controllers/teacher/ParentGuardiansController.php',
        type: 'POST',
        data: {
            search_available_students: 1,
            keyword: keyword.trim()
        },
        dataType: 'json',
        success: function (data) {
            resultsContainer.innerHTML = '';

            if (data.length === 0) {
                resultsContainer.innerHTML = '<div class="list-group-item text-muted small">No available students found.</div>';
            } else {
                data.forEach(function (student) {
                    const fullName = [student.first_name, student.middle_name, student.last_name]
                        .filter(Boolean).join(' ');
                    const lrn = student.lrn ? ` — LRN: ${student.lrn}` : '';

                    const item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'list-group-item list-group-item-action small';
                    item.textContent = fullName + lrn;
                    item.addEventListener('click', function () {
                        selectStudent(student.id, fullName);
                    });

                    resultsContainer.appendChild(item);
                });
            }

            resultsContainer.style.display = 'block';
            if (warning) warning.style.display = 'none';
        },
        error: function () {
            resultsContainer.innerHTML = '<div class="list-group-item text-danger small">Search error.</div>';
            resultsContainer.style.display = 'block';
        }
    });
}

/**
 * Select a student from search results
 * @param {number} studentId
 * @param {string} fullName
 */
function selectStudent(studentId, fullName) {
    document.getElementById('add_student_id').value = studentId;
    document.getElementById('studentSearchInput').value = fullName;
    document.getElementById('studentSearchResults').style.display = 'none';
    document.getElementById('studentSearchResults').innerHTML = '';

    $.ajax({
        url: '../../../app/controllers/teacher/ParentGuardiansController.php',
        type: 'POST',
        data: {
            check_guardian_exists: 1,
            student_id: studentId
        },
        dataType: 'json',
        success: function (data) {
            const warning = document.getElementById('duplicateWarning');
            const submitBtn = document.querySelector('#addGuardianModal button[name="save_guardian"]');

            if (data.exists) {
                if (warning) warning.style.display = 'block';
                if (submitBtn) submitBtn.disabled = true;
                document.getElementById('add_student_id').value = '';
            } else {
                if (warning) warning.style.display = 'none';
                if (submitBtn) submitBtn.disabled = false;
            }
        }
    });
}

/**
 * Initialize student search inside Add Guardian modal
 */
function initializeStudentSearch() {
    const input = document.getElementById('studentSearchInput');
    if (!input) return;

    input.addEventListener('keyup', function () {
        const keyword = this.value.trim();
        clearTimeout(studentSearchTimer);

        if (keyword === '') {
            document.getElementById('add_student_id').value = '';
            document.getElementById('studentSearchResults').style.display = 'none';
            return;
        }

        studentSearchTimer = setTimeout(function () {
            searchAvailableStudents(keyword);
        }, 300);
    });

    document.addEventListener('click', function (e) {
        if (!input.contains(e.target)) {
            document.getElementById('studentSearchResults').style.display = 'none';
        }
    });

    const modal = document.getElementById('addGuardianModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function () {
            document.getElementById('studentSearchInput').value = '';
            document.getElementById('add_student_id').value = '';
            document.getElementById('studentSearchResults').style.display = 'none';
            document.getElementById('studentSearchResults').innerHTML = '';
            const warning = document.getElementById('duplicateWarning');
            if (warning) warning.style.display = 'none';
            const submitBtn = document.querySelector('#addGuardianModal button[name="save_guardian"]');
            if (submitBtn) submitBtn.disabled = false;
        });
    }
}

document.addEventListener('DOMContentLoaded', function () {
    initializeSearch();
    initializeStudentSearch();
});

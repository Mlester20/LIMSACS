function editParentGuardian(id, studentId, studentName, fatherName, fatherOccupation, fatherContact, motherName, motherOccupation, motherContact, guardianName, guardianRelationship, guardianContact) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_student_id').value = studentId || '';
    document.getElementById('editStudentSearchInput').value = studentName || '';
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
// Search Functionality
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
 * Perform parent/guardian search via API
 * @param {string} keyword - Search keyword
 */
async function searchGuardians(keyword) {
    const tableBody = document.querySelector('table tbody');
    const paginationContainer = document.querySelector('.card-footer');

    // Validate keyword
    if (!keyword || keyword.trim().length < 2) {
        // Reset to show all guardians if keyword is empty or too short
        if (keyword === '') {
            location.reload();
        }
        return;
    }

    try {
        // Disable pagination during search
        if (paginationContainer) {
            paginationContainer.style.display = 'none';
        }

        // Show loading state
        if (tableBody) {
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center"><span class="spinner-border spinner-border-sm me-2"></span>Searching...</td></tr>';
        }

        // Build API URL with absolute path
        const baseUrl = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/resources'));
        const apiUrl = baseUrl + '/app/api/registrar/search-guardians.php?q=' + encodeURIComponent(keyword.trim());

        // Fetch search results
        const response = await fetch(apiUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw new Error(`API error: ${response.status}`);
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || 'Search failed');
        }

        // Render search results
        renderSearchResults(data.data || []);

    } catch (error) {
        console.error('Search error:', error);
        
        // Show error message
        if (tableBody) {
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error searching guardians. Please try again.</td></tr>';
        }
    }
}

/**
 * Render search results in the table
 * @param {Array} results - Search results from API
 */
function renderSearchResults(results) {
    const tableBody = document.querySelector('table tbody');
    const paginationContainer = document.querySelector('.card-footer');

    if (!tableBody) return;

    // Clear existing content
    tableBody.innerHTML = '';

    if (results.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="6" class="text-center">No guardians found matching your search.</td></tr>';
        if (paginationContainer) {
            paginationContainer.style.display = 'none';
        }
        return;
    }

    const rows = results.map(guardian => {
        const studentName = `${guardian.first_name || ''} ${guardian.last_name || ''}`.trim();
        const guardianName = guardian.guardian_name || 'N/A';
        const relationship = guardian.guardian_relationship || 'N/A';
        const contactNumber = guardian.guardian_contact || 'N/A';

        return `
            <tr>
                <td>${escapeHtml(guardian.id)}</td>
                <td>${escapeHtml(studentName)}</td>
                <td>${escapeHtml(guardianName)}</td>
                <td>${escapeHtml(relationship)}</td>
                <td>${escapeHtml(contactNumber)}</td>
                <td>
                    <button 
                        class="btn btn-sm btn-info"
                        data-bs-toggle="modal"
                        data-bs-target="#viewGuardianModal"
                        onclick="viewGuardian(
                            '${escapeJsString(studentName)}',
                            '${escapeJsString(guardian.father_name || '')}',
                            '${escapeJsString(guardian.father_occupation || '')}',
                            '${escapeJsString(guardian.father_contact || '')}',
                            '${escapeJsString(guardian.mother_name || '')}',
                            '${escapeJsString(guardian.mother_occupation || '')}',
                            '${escapeJsString(guardian.mother_contact || '')}',
                            '${escapeJsString(guardian.guardian_name || '')}',
                            '${escapeJsString(guardian.guardian_relationship || '')}',
                            '${escapeJsString(guardian.guardian_contact || '')}',
                            '${escapeJsString(guardian.monthly_income || '')}'
                        )" 
                    >
                        View
                    </button>

                    <button 
                        class="btn btn-sm btn-warning"
                        data-bs-toggle="modal"
                        data-bs-target="#editGuardianModal"
                        onclick="editGuardian(
                            ${guardian.id},
                            '${escapeJsString(guardian.student_id || '')}',
                            '${escapeJsString(guardian.father_name || '')}',
                            '${escapeJsString(guardian.father_occupation || '')}',
                            '${escapeJsString(guardian.father_contact || '')}',
                            '${escapeJsString(guardian.mother_name || '')}',
                            '${escapeJsString(guardian.mother_occupation || '')}',
                            '${escapeJsString(guardian.mother_contact || '')}',
                            '${escapeJsString(guardian.guardian_name || '')}',
                            '${escapeJsString(guardian.guardian_relationship || '')}',
                            '${escapeJsString(guardian.guardian_contact || '')}',
                            '${escapeJsString(guardian.monthly_income || '')}'
                        )" 
                    >
                        Edit
                    </button>

                    <form action="../../../app/controllers/registrar/ParentGuardiansController.php" method="post" style="display: inline;">
                        <input type="hidden" name="guardian_id" value="${guardian.id}">
                        <button 
                            type="submit" 
                            name="deleteGuardian" 
                            class="btn btn-sm btn-danger" 
                            onclick="return confirm('Are you sure you want to delete this guardian?');"
                        >
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
        `;
    }).join('');

    // Insert rows into table
    tableBody.innerHTML = rows;

    // Hide pagination during search results
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
 * Escape text for safe use in JavaScript strings
 * @param {string} text - Text to escape
 */
function escapeJsString(text) {
    if (!text) return '';
    return String(text)
        .replace(/\\/g, '\\\\')
        .replace(/'/g, "\\'")
        .replace(/"/g, '\\"')
        .replace(/\n/g, '\\n')
        .replace(/\r/g, '\\r');
}

/**
 * Initialize search functionality
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

document.addEventListener('DOMContentLoaded', initializeSearch);

// ========================================
// Add Guardian: Student Search
// ========================================

let studentSearchTimer = null;

/**
 * Search students with no existing guardian record
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
        url: '../../../app/controllers/registrar/ParentGuardiansController.php',
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

    // Double-check server-side for duplicate
    $.ajax({
        url: '../../../app/controllers/registrar/ParentGuardiansController.php',
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

    // Close dropdown when clicking outside
    document.addEventListener('click', function (e) {
        if (!input.contains(e.target)) {
            document.getElementById('studentSearchResults').style.display = 'none';
        }
    });

    // Reset on modal close
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
    initializeSearch();         // existing: main table search
    initializeStudentSearch();  // new: student search inside add modal
    initializeEditStudentSearch(); // new: student search inside edit modal
});

// ========================================
// Edit Guardian: Student Search
// ========================================

let editStudentSearchTimer = null;

/**
 * Search students for edit modal (may allow reassigning to different student)
 * @param {string} keyword
 */
function searchStudentsForEdit(keyword) {
    const resultsContainer = document.getElementById('editStudentSearchResults');
    const warning = document.getElementById('editDuplicateWarning');

    if (!keyword || keyword.trim().length < 2) {
        resultsContainer.style.display = 'none';
        resultsContainer.innerHTML = '';
        return;
    }

    $.ajax({
        url: '../../../app/controllers/registrar/ParentGuardiansController.php',
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
                        selectEditStudent(student.id, fullName);
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
 * Select a student from edit modal search results
 * @param {number} studentId
 * @param {string} fullName
 */
function selectEditStudent(studentId, fullName) {
    document.getElementById('edit_student_id').value = studentId;
    document.getElementById('editStudentSearchInput').value = fullName;
    document.getElementById('editStudentSearchResults').style.display = 'none';
    document.getElementById('editStudentSearchResults').innerHTML = '';

    // Double-check server-side for duplicate
    $.ajax({
        url: '../../../app/controllers/registrar/ParentGuardiansController.php',
        type: 'POST',
        data: {
            check_guardian_exists: 1,
            student_id: studentId
        },
        dataType: 'json',
        success: function (data) {
            const warning = document.getElementById('editDuplicateWarning');
            const submitBtn = document.querySelector('#editGuardianModal button[name="update_guardian"]');

            if (data.exists) {
                if (warning) warning.style.display = 'block';
                if (submitBtn) submitBtn.disabled = true;
                document.getElementById('edit_student_id').value = '';
            } else {
                if (warning) warning.style.display = 'none';
                if (submitBtn) submitBtn.disabled = false;
            }
        }
    });
}

/**
 * Initialize student search inside Edit Guardian modal
 */
function initializeEditStudentSearch() {
    const input = document.getElementById('editStudentSearchInput');
    if (!input) return;

    input.addEventListener('keyup', function () {
        const keyword = this.value.trim();
        clearTimeout(editStudentSearchTimer);

        if (keyword === '') {
            document.getElementById('edit_student_id').value = '';
            document.getElementById('editStudentSearchResults').style.display = 'none';
            return;
        }

        editStudentSearchTimer = setTimeout(function () {
            searchStudentsForEdit(keyword);
        }, 300);
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function (e) {
        if (!input.contains(e.target)) {
            document.getElementById('editStudentSearchResults').style.display = 'none';
        }
    });

    // Reset on modal close
    const modal = document.getElementById('editGuardianModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function () {
            document.getElementById('editStudentSearchInput').value = '';
            document.getElementById('edit_student_id').value = '';
            document.getElementById('editStudentSearchResults').style.display = 'none';
            document.getElementById('editStudentSearchResults').innerHTML = '';
            const warning = document.getElementById('editDuplicateWarning');
            if (warning) warning.style.display = 'none';
            const submitBtn = document.querySelector('#editGuardianModal button[name="update_guardian"]');
            if (submitBtn) submitBtn.disabled = false;
        });
    }
}
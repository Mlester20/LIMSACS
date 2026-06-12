function editParentGuardian(id, studentId, fatherName, fatherOccupation, fatherContact, motherName, motherOccupation, motherContact, guardianName, guardianRelationship, guardianContact) {
    document.getElementById('edit_id').value = id;
    document.getElementById('editStudentId').value = studentId || '';
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
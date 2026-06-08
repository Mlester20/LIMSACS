function editFunction(id, section_name, grade_level, adviser_id, school_year_id) {
    document.getElementById('edit_section_id').value = id;
    document.getElementById('edit_section_name').value = section_name;
    document.getElementById('edit_section_grade_level').value = grade_level; 
    document.getElementById('edit_adviser_id').value = adviser_id;
    document.getElementById('edit_school_year_id').value = school_year_id; 
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
 * Perform section search via API
 * @param {string} keyword - Search keyword
 */
async function searchSection(keyword) {
    const tableBody = document.querySelector('table tbody');
    const paginationContainer = document.querySelector('.card-footer');

    // Validate keyword
    if (!keyword || keyword.trim().length < 2) {
        // Reset to show all sections if keyword is empty or too short
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
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center"><span class="spinner-border spinner-border-sm me-2"></span>Searching...</td></tr>';
        }

        // Build API URL with absolute path
        const baseUrl = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/resources'));
        const apiUrl = baseUrl + '/app/api/registrar/search-section.php?q=' + encodeURIComponent(keyword.trim());

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
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error searching sections. Please try again.</td></tr>';
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
        tableBody.innerHTML = '<tr><td colspan="7" class="text-center">No sections found matching your search.</td></tr>';
        if (paginationContainer) {
            paginationContainer.style.display = 'none';
        }
        return;
    }

    const rows = results.map((section, index) => {
        const sectionId = section.id || '';
        const sectionName = section.section_name || 'N/A';
        const gradeLevel = section.grade_level || 'N/A';
        const adviserName = section.adviser_name || 'N/A';
        const schoolYear = section.school_year || 'N/A';
        const totalStudents = section.total_students || 0;

        return `
            <tr>
                <td>${escapeHtml(index + 1)}</td>
                <td>${escapeHtml(sectionName)}</td>
                <td>${escapeHtml(gradeLevel)}</td>
                <td>${escapeHtml(adviserName)}</td>
                <td>${escapeHtml(schoolYear)}</td>
                <td>${escapeHtml(totalStudents)}</td>
                <td>
                    <button 
                        class="btn btn-sm btn-primary me-1"
                        title="Edit Section"
                        data-bs-toggle="modal" 
                        data-bs-target="#editSectionModal"
                        onclick="editFunction(
                            ${sectionId},
                            '${sectionName.replace(/'/g, "\\'").replace(/"/g, '\\"')}',
                            '${gradeLevel.replace(/'/g, "\\'").replace(/"/g, '\\"')}',
                            ${section.adviser_id},
                            ${section.school_year_id}
                        )"
                    >
                        Edit
                    </button>

                    <button 
                        class="btn btn-sm btn-danger" 
                        title="Delete Section"
                        onclick="if(confirm('Are you sure you want to delete this section?')) {
                            fetch('../../../app/controllers/registrar/SectionsController.php', {
                                method: 'POST',
                                body: new URLSearchParams({delete_section: ${sectionId}})
                            }).then(r => location.reload()).catch(e => alert('Error: ' + e));
                        }"
                    >
                        Delete
                    </button>
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
 * Initialize search functionality
 */
function initializeSearch() {
    const searchInput = document.getElementById('searchInput');
    
    if (!searchInput) return;

    const debouncedSearch = debounce((e) => {
        const keyword = e.target.value.trim();
        searchSection(keyword);
    }, 300);

    searchInput.addEventListener('input', debouncedSearch);
}

document.addEventListener('DOMContentLoaded', initializeSearch);
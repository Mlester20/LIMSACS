/**
 * Read the CSRF token rendered into the page's <meta name="csrf-token"> tag,
 * needed for requests built dynamically in JS (e.g. search result rows).
 */
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

function editFunction(id, section_name, grade_level, adviser_id, school_year_id) {
    document.getElementById('edit_section_id').value = id;
    document.getElementById('edit_section_name').value = section_name;
    document.getElementById('edit_section_grade_level').value = grade_level; 
    document.getElementById('edit_adviser_id').value = adviser_id;
    document.getElementById('edit_school_year_id').value = school_year_id; 
}

function viewSection(id, section_name, grade_level, adviser_name, school_year, total_students, max_capacity) {
    document.getElementById('view_section_name').textContent = section_name;
    document.getElementById('view_section_grade_level').textContent = grade_level;
    document.getElementById('view_adviser_name').textContent = adviser_name;
    document.getElementById('view_school_year').textContent = school_year;

    const total = parseInt(total_students) || 0;
    const max = parseInt(max_capacity) || 35;
    document.getElementById('view_total_students').textContent = total;
    document.getElementById('view_max_capacity').textContent = max;
    document.getElementById('view_capacity_info').textContent = total + ' / ' + max;

    const percentage = Math.round((total / max) * 100);

    // Progress bar
    const progressBar = document.getElementById('view_capacity_progress');
    if (progressBar) {
        progressBar.style.width = percentage + '%';
        progressBar.setAttribute('aria-valuenow', percentage);
        progressBar.classList.remove('bg-success', 'bg-warning', 'bg-danger');

        if (percentage >= 90) {
            progressBar.classList.add('bg-danger');
        } else if (percentage >= 70) {
            progressBar.classList.add('bg-warning');
        } else {
            progressBar.classList.add('bg-success');
        }
    }

    // Percent label inside bar (only show if wide enough)
    const percentLabel = document.getElementById('view_capacity_percent');
    if (percentLabel) {
        percentLabel.textContent = percentage >= 10 ? percentage + '%' : '';
    }

    // "X% full" label below bar
    const capacityLabel = document.getElementById('view_capacity_label');
    if (capacityLabel) {
        capacityLabel.textContent = percentage + '% full';

        capacityLabel.className = 'mb-0';
        capacityLabel.style.fontSize = '12px';

        if (percentage >= 90) {
            capacityLabel.classList.add('text-danger');
        } else if (percentage >= 70) {
            capacityLabel.classList.add('text-warning');
        } else {
            capacityLabel.classList.add('text-success');
        }
    }

    // Capacity info color (top-right "X / 35")
    const capacityInfo = document.getElementById('view_capacity_info');
    if (capacityInfo) {
        capacityInfo.className = 'fw-semibold';
        capacityInfo.style.fontSize = '15px';

        if (percentage >= 90) {
            capacityInfo.classList.add('text-danger');
        } else if (percentage >= 70) {
            capacityInfo.classList.add('text-warning');
        } else {
            capacityInfo.classList.add('text-success');
        }
    }
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
                <td><span class="badge bg-primary">${escapeHtml(totalStudents)}</span></td>
                <td>
                    <button 
                        class="btn btn-sm btn-primary me-1"
                        title="View Section"
                        data-bs-toggle="modal" 
                        data-bs-target="#viewSectionModal"
                        onclick="viewSection(
                            ${sectionId},
                            '${sectionName.replace(/'/g, "\\'").replace(/"/g, '\\"')}',
                            '${gradeLevel.replace(/'/g, "\\'").replace(/"/g, '\\"')}',
                            '${adviserName.replace(/'/g, "\\'").replace(/"/g, '\\"')}',
                            '${schoolYear.replace(/'/g, "\\'").replace(/"/g, '\\"')}',
                            ${totalStudents},
                            ${section.max_capacity || 35}
                        )"
                    >
                        View
                    </button>

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
                                body: new URLSearchParams({delete_section: ${sectionId}, csrf_token: getCsrfToken()})
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
// Function to populate modal with student data
function populateStudentModal(student) {
    // Personal Information
    document.getElementById('modalLrn').textContent = student.lrn || '-';
    document.getElementById('modalFirstName').textContent = student.first_name || '-';
    document.getElementById('modalMiddleName').textContent = student.middle_name || '-';
    document.getElementById('modalLastName').textContent = student.last_name || '-';
    document.getElementById('modalGender').textContent = student.gender || '-';

    // Birth Information
    document.getElementById('modalBirthDate').textContent = student.birth_date || '-';
    document.getElementById('modalAge').textContent = student.age || '-';
    document.getElementById('modalPlaceOfBirth').textContent = student.place_of_birth || '-';

    // Contact Information
    document.getElementById('modalContactNumber').textContent = student.contact_number || '-';
    document.getElementById('modalAddress').textContent = student.address || '-';

    // Other Information
    document.getElementById('modalReligion').textContent = student.religion || '-';

    // Status Badge
    const statusBadge = document.getElementById('modalStatusBadge');
    const status = student.student_status || 'Active';

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
        const baseUrl = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/resources'));
        const apiUrl = baseUrl + '/app/api/registrar/search-students.php?q=' + encodeURIComponent(keyword.trim());

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
        const studentName = `${student.first_name || ''} ${student.middle_name || ''} ${student.last_name || ''}`.trim();
        const lrn = student.lrn || 'N/A';
        const enrollmentStatus = student.enrollment_status || 'N/A';

        return `
            <tr>
                <td>${escapeHtml(index + 1)}</td>
                <td>${escapeHtml(lrn)}</td>
                <td>${escapeHtml(studentName)}</td>
                <td>${escapeHtml(enrollmentStatus)}</td>
                <td>
                    <button 
                        class="btn btn-sm btn-warning me-1"
                        title="Edit Student"
                        onclick="editStudent(${student.id})"
                    >
                        Edit
                    </button>

                    <button 
                        class="btn btn-sm btn-danger" 
                        title="Delete Student"
                        onclick="if(confirm('Are you sure you want to delete this student?')) deleteStudent(${student.id})"
                    >
                        Delete
                    </button>
                </td>
            </tr>
        `;
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
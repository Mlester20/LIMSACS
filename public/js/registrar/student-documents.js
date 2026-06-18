function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// ============== PAGINATION VARIABLES ==============
let allDocumentRows = [];
let filteredRows = [];
let currentPage = 1;
let itemsPerPage = 15;

// ============== PAGINATION FUNCTIONS ==============

function initializePagination() {
    allDocumentRows = Array.from(document.querySelectorAll('#documentsTableBody tr.document-row'));
    filteredRows = [...allDocumentRows];
    
    // Set initial items per page
    const itemsPerPageSelect = document.getElementById('itemsPerPage');
    if (itemsPerPageSelect) {
        itemsPerPageSelect.addEventListener('change', function() {
            itemsPerPage = parseInt(this.value);
            currentPage = 1;
            updatePagination();
        });
    }
    
    updatePagination();
}

function updatePagination() {
    const totalPages = Math.max(1, Math.ceil(filteredRows.length / itemsPerPage));
    
    // Ensure current page is valid
    if (currentPage > totalPages) {
        currentPage = totalPages;
    }
    if (currentPage < 1) {
        currentPage = 1;
    }
    
    // Hide all rows first
    allDocumentRows.forEach(row => {
        row.style.display = 'none';
    });
    
    // Show only rows for current page
    const startIdx = (currentPage - 1) * itemsPerPage;
    const endIdx = startIdx + itemsPerPage;
    
    for (let i = startIdx; i < endIdx && i < filteredRows.length; i++) {
        filteredRows[i].style.display = '';
    }
    
    // Update pagination info
    const totalFilteredRows = filteredRows.length;
    const showingStart = totalFilteredRows === 0 ? 0 : startIdx + 1;
    const showingEnd = Math.min(endIdx, totalFilteredRows);
    
    document.getElementById('showingStart').textContent = showingStart;
    document.getElementById('showingEnd').textContent = showingEnd;
    document.getElementById('totalEntries').textContent = totalFilteredRows;
    
    // Generate pagination buttons
    generatePaginationButtons(totalPages);
}

function generatePaginationButtons(totalPages) {
    const paginationList = document.getElementById('paginationList');
    paginationList.innerHTML = '';
    
    // Handle no results case
    if (totalPages === 0) {
        return;
    }
    
    // Previous button
    const prevLi = document.createElement('li');
    prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
    prevLi.innerHTML = `<a class="page-link" href="#" onclick="goToPage(${currentPage - 1}); return false;">Previous</a>`;
    paginationList.appendChild(prevLi);
    
    // Determine which page numbers to show
    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, currentPage + 2);
    
    // Show "first page" link if we're not showing page 1
    if (startPage > 1) {
        const firstLi = document.createElement('li');
        firstLi.className = 'page-item';
        firstLi.innerHTML = `<a class="page-link" href="#" onclick="goToPage(1); return false;">1</a>`;
        paginationList.appendChild(firstLi);
        
        if (startPage > 2) {
            const dotsLi = document.createElement('li');
            dotsLi.className = 'page-item disabled';
            dotsLi.innerHTML = `<span class="page-link">...</span>`;
            paginationList.appendChild(dotsLi);
        }
    }
    
    // Page numbers
    for (let i = startPage; i <= endPage; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${i === currentPage ? 'active' : ''}`;
        li.innerHTML = `<a class="page-link" href="#" onclick="goToPage(${i}); return false;">${i}</a>`;
        paginationList.appendChild(li);
    }
    
    // Show "last page" link if we're not showing the last page
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            const dotsLi = document.createElement('li');
            dotsLi.className = 'page-item disabled';
            dotsLi.innerHTML = `<span class="page-link">...</span>`;
            paginationList.appendChild(dotsLi);
        }
        
        const lastLi = document.createElement('li');
        lastLi.className = 'page-item';
        lastLi.innerHTML = `<a class="page-link" href="#" onclick="goToPage(${totalPages}); return false;">${totalPages}</a>`;
        paginationList.appendChild(lastLi);
    }
    
    // Next button
    const nextLi = document.createElement('li');
    nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
    nextLi.innerHTML = `<a class="page-link" href="#" onclick="goToPage(${currentPage + 1}); return false;">Next</a>`;
    paginationList.appendChild(nextLi);
}

function goToPage(pageNumber) {
    const totalPages = Math.max(1, Math.ceil(filteredRows.length / itemsPerPage));
    if (pageNumber >= 1 && pageNumber <= totalPages) {
        currentPage = pageNumber;
        updatePagination();
        // Scroll to top of table
        document.querySelector('.table-responsive')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// ============== ADD DOCUMENT MODAL ==============

// Debounced search for add modal
const addSearchStudents = debounce(function() {
    const keyword = document.getElementById('addStudentSearch').value.trim();
    const resultsContainer = document.getElementById('addStudentResults');

    if (keyword.length < 2) {
        resultsContainer.style.display = 'none';
        resultsContainer.innerHTML = '';
        return;
    }

    $.ajax({
        url: 'student-documents.php?action=search_students&keyword=' + encodeURIComponent(keyword),
        type: 'GET',
        dataType: 'json',
        success: function(results) {
            if (results.length > 0) {
                let html = '';
                results.forEach(student => {
                    const fullName = [student.first_name, student.middle_name, student.last_name]
                        .filter(n => n && n.trim() !== '')
                        .join(' ');
                    html += `
                        <a href="#" class="list-group-item list-group-item-action px-2 py-1" 
                           onclick="selectAddStudent({
                               id: ${student.id},
                               first_name: '${student.first_name.replace(/'/g, "\\'")}',
                               middle_name: '${student.middle_name ? student.middle_name.replace(/'/g, "\\'") : ''}',
                               last_name: '${student.last_name.replace(/'/g, "\\'")}',
                               lrn: '${student.lrn.replace(/'/g, "\\'")}'
                           }); return false;">
                            <strong>${fullName}</strong> (LRN: ${student.lrn})
                        </a>
                    `;
                });
                resultsContainer.innerHTML = html;
                resultsContainer.style.display = 'block';
            } else {
                resultsContainer.innerHTML = '<div class="list-group-item px-2 py-1 text-muted">No students found</div>';
                resultsContainer.style.display = 'block';
            }
        },
        error: function(error) {
            console.error('Error searching students:', error);
            resultsContainer.innerHTML = '<div class="list-group-item px-2 py-1 text-danger">Error loading results</div>';
            resultsContainer.style.display = 'block';
        }
    });
}, 300);

// Event listener for add student search
document.getElementById('addStudentSearch').addEventListener('keyup', addSearchStudents);

// Select student from add search results
function selectAddStudent(student) {
    const fullName = [student.first_name, student.middle_name, student.last_name]
        .filter(n => n && n.trim() !== '')
        .join(' ');
    
    document.getElementById('addStudentSearch').value = fullName + ' (LRN: ' + student.lrn + ')';
    document.getElementById('add_student_id').value = student.id;
    document.getElementById('addStudentResults').style.display = 'none';
}

// Clear add modal when it's hidden
document.getElementById('addDocumentModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('addDocumentForm').reset();
    document.getElementById('addStudentSearch').value = '';
    document.getElementById('add_student_id').value = '';
    document.getElementById('addStudentResults').style.display = 'none';
});

// Validate and submit add document form
document.getElementById('addDocumentForm').addEventListener('submit', function(e) {
    const studentId = document.getElementById('add_student_id').value;
    if (!studentId) {
        e.preventDefault();
        alert('Please select a student before submitting.');
        return false;
    }
});

// ============== FILE PREVIEW ERROR HANDLING ==============

// Handle image loading errors
function handleImageError(img) {
    const container = img.closest('[id^="currentFilePreview"]') || img.parentElement;
    container.innerHTML = `
        <div class="alert alert-warning py-2 px-3 mb-0" role="alert">
            <small>
                <i class="bx bx-error-circle me-1"></i>
                <strong>Image not found.</strong> The file may have been deleted or moved.
                <a href="${img.parentElement.href}" target="_blank" class="alert-link">Try downloading it</a>
            </small>
        </div>
    `;
}

// Handle PDF loading errors
function handlePdfError() {
    const pdfContainer = document.getElementById('pdfPreviewContainer');
    if (pdfContainer) {
        pdfContainer.innerHTML = `
            <div class="alert alert-warning py-2 px-3 mb-0" role="alert">
                <small>
                    <i class="bx bx-error-circle me-1"></i>
                    <strong>PDF cannot be previewed.</strong> The file may have been deleted or moved.
                </small>
            </div>
        `;
    }
}

// Check if file exists before loading
function checkFileExists(filePath, fileType, previewContainer) {
    fetch(filePath, { method: 'HEAD' })
        .then(response => {
            if (!response.ok) {
                showFileNotFoundError(fileType, previewContainer, filePath);
            }
        })
        .catch(() => {
            showFileNotFoundError(fileType, previewContainer, filePath);
        });
}

// Show file not found error
function showFileNotFoundError(fileType, previewContainer, filePath) {
    const fileName = filePath.split('/').pop();
    const downloadText = fileType === 'pdf' ? 'Download' : 'View';
    
    previewContainer.innerHTML = `
        <div class="alert alert-danger py-2 px-3 mb-0" role="alert">
            <small>
                <i class="bx bx-error-circle me-1"></i>
                <strong>File not found!</strong> The requested resource "<code>${fileName}</code>" was not found on this server.
                The file may have been deleted or moved.
            </small>
        </div>
    `;
}

// ============== EDIT DOCUMENT MODAL ==============

// Debounced search for edit modal
const editSearchStudents = debounce(function() {
    const keyword = document.getElementById('editStudentSearch').value.trim();
    const resultsContainer = document.getElementById('editStudentResults');

    if (keyword.length < 2) {
        resultsContainer.style.display = 'none';
        resultsContainer.innerHTML = '';
        return;
    }

    $.ajax({
        url: 'student-documents.php?action=search_students&keyword=' + encodeURIComponent(keyword),
        type: 'GET',
        dataType: 'json',
        success: function(results) {
            if (results.length > 0) {
                let html = '';
                results.forEach(student => {
                    const fullName = [student.first_name, student.middle_name, student.last_name]
                        .filter(n => n && n.trim() !== '')
                        .join(' ');
                    html += `
                        <a href="#" class="list-group-item list-group-item-action px-2 py-1" 
                           onclick="selectEditStudent({
                               id: ${student.id},
                               first_name: '${student.first_name.replace(/'/g, "\\'")}',
                               middle_name: '${student.middle_name ? student.middle_name.replace(/'/g, "\\'") : ''}',
                               last_name: '${student.last_name.replace(/'/g, "\\'")}',
                               lrn: '${student.lrn.replace(/'/g, "\\'")}'
                           }); return false;">
                            <strong>${fullName}</strong> (LRN: ${student.lrn})
                        </a>
                    `;
                });
                resultsContainer.innerHTML = html;
                resultsContainer.style.display = 'block';
            } else {
                resultsContainer.innerHTML = '<div class="list-group-item px-2 py-1 text-muted">No students found</div>';
                resultsContainer.style.display = 'block';
            }
        },
        error: function(error) {
            console.error('Error searching students:', error);
            resultsContainer.innerHTML = '<div class="list-group-item px-2 py-1 text-danger">Error loading results</div>';
            resultsContainer.style.display = 'block';
        }
    });
}, 300);

// Event listener for edit student search
document.getElementById('editStudentSearch').addEventListener('keyup', editSearchStudents);

// Select student from edit search results
function selectEditStudent(student) {
    const fullName = [student.first_name, student.middle_name, student.last_name]
        .filter(n => n && n.trim() !== '')
        .join(' ');
    
    document.getElementById('editStudentSearch').value = fullName + ' (LRN: ' + student.lrn + ')';
    document.getElementById('edit_student_id').value = student.id;
    document.getElementById('editStudentResults').style.display = 'none';
}

// Validate and submit edit document form
document.getElementById('editDocumentForm').addEventListener('submit', function(e) {
    const studentId = document.getElementById('edit_student_id').value;
    if (!studentId) {
        e.preventDefault();
        alert('Please select a student before submitting.');
        return false;
    }
});

// ============== EDIT DOCUMENT FUNCTION ==============

function editDocument(documentData) {
    console.log("DOCUMENT DATA:", documentData);
    // Populate form fields with document data
    document.getElementById('edit_document_id').value = documentData.id;
    document.getElementById('edit_student_id').value = documentData.student_id;
    document.getElementById('edit_document_type_id').value = documentData.document_type_id;
    document.getElementById('edit_status').value = documentData.status;
    document.getElementById('edit_remarks').value = documentData.remarks || '';

    // Display current file information + preview
    const currentFileInfo = document.getElementById('currentFileInfo');
    const currentFileName = document.getElementById('currentFileName');
    const currentFilePreview = document.getElementById('currentFilePreview');

    if (documentData.file_path) {
        // Normalize file path to use /storage/student_documents/ absolute path
        let normalizedPath = documentData.file_path;
        if (normalizedPath.includes('storage/student_documents/')) {
            const fileName = normalizedPath.split('/').pop();
            normalizedPath = '/storage/student_documents/' + fileName;
        }
        
        const fileName = normalizedPath.split('/').pop();
        const ext = fileName.split('.').pop().toLowerCase();
        currentFileName.textContent = fileName;
        currentFileInfo.style.display = 'block';

        // Build preview based on file type
        if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
            currentFilePreview.innerHTML = `
                <a href="${normalizedPath}" target="_blank">
                    <img src="${normalizedPath}" alt="Document preview"
                         style="max-width:120px; max-height:120px; border-radius:6px;
                                border:1px solid #dee2e6; object-fit:contain; cursor:pointer;"
                         onerror="handleImageError(this)"
                         title="Click to open full size">
                </a>
                <div class="text-muted small mt-1">Click to expand</div>
            `;
            currentFilePreview.style.display = 'block';
        } else if (ext === 'pdf') {
            currentFilePreview.innerHTML = `
                <a href="${normalizedPath}" target="_blank" style="display: inline-block; text-decoration: none;">
                    <div style="width:120px; height:120px; border:1px solid #dee2e6; border-radius:6px; 
                                background:#f8f9fa; display:flex; align-items:center; justify-content:center;
                                cursor:pointer; transition: all 0.2s ease;"
                         onmouseover="this.style.background='#e7f3ff'; this.style.borderColor='#0d6efd';"
                         onmouseout="this.style.background='#f8f9fa'; this.style.borderColor='#dee2e6';">
                        <i class="bx bx-file-pdf" style="font-size:3rem; color:#dc3545;"></i>
                    </div>
                </a>
                <div class="text-muted small mt-1">Click to open</div>
            `;
            currentFilePreview.style.display = 'block';
        } else {
            // For DOC/DOCX and other types, just show a download link
            currentFilePreview.innerHTML = `
                <a href="${normalizedPath}" target="_blank" class="btn btn-sm btn-outline-secondary">
                    <i class="bx bx-download me-1"></i> Download / View File
                </a>
            `;
            currentFilePreview.style.display = 'block';
        }
    } else {
        currentFileInfo.style.display = 'none';
        if (currentFilePreview) {
            currentFilePreview.innerHTML = '';
            currentFilePreview.style.display = 'none';
        }
    }

    // Pre-fill student search box with current student info
    const fullName = [documentData.student_first_name, documentData.student_last_name]
        .filter(n => n && n.trim() !== '')
        .join(' ');
    document.getElementById('editStudentSearch').value = fullName;
    document.getElementById('editStudentResults').style.display = 'none';
}

// Live preview when a new file is chosen in the edit modal
document.addEventListener('DOMContentLoaded', function () {
    const editFileInput = document.getElementById('edit_file_path');
    if (editFileInput) {
        editFileInput.addEventListener('change', function () {
            const file = this.files[0];
            const currentFilePreview = document.getElementById('currentFilePreview');
            const currentFileInfo   = document.getElementById('currentFileInfo');
            const currentFileName   = document.getElementById('currentFileName');
            if (!file || !currentFilePreview) return;

            const ext = file.name.split('.').pop().toLowerCase();
            currentFileName.textContent = file.name + ' (new)';
            currentFileInfo.style.display = 'block';

            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    currentFilePreview.innerHTML = `
                        <img src="${e.target.result}" alt="New file preview"
                             style="max-width:120px; max-height:120px; border-radius:6px;
                                    border:1px solid #dee2e6; object-fit:contain; cursor:pointer;"
                             title="New image selected">
                        <div class="text-muted small mt-1">New image selected</div>
                    `;
                    currentFilePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else if (ext === 'pdf') {
                currentFilePreview.innerHTML = `
                    <div style="width:120px; height:120px; border:1px solid #dee2e6; border-radius:6px; 
                                background:#f8f9fa; display:flex; align-items:center; justify-content:center;">
                        <i class="bx bx-file-pdf" style="font-size:3rem; color:#dc3545;"></i>
                    </div>
                    <div class="text-muted small mt-1">New PDF selected</div>
                `;
                currentFilePreview.style.display = 'block';
            } else {
                currentFilePreview.innerHTML = `
                    <div class="text-muted small">
                        <i class="bx bx-file-earmark me-1"></i><strong>${file.name}</strong> selected
                    </div>
                `;
                currentFilePreview.style.display = 'block';
            }
        });
    }
    
    // Initialize pagination
    initializePagination();
});

// Clear edit modal when it's hidden
document.getElementById('editDocumentModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('editDocumentForm').reset();
    document.getElementById('editStudentSearch').value = '';
    document.getElementById('edit_student_id').value = '';
    document.getElementById('editStudentResults').style.display = 'none';
    document.getElementById('currentFileInfo').style.display = 'none';
    const preview = document.getElementById('currentFilePreview');
    if (preview) { preview.innerHTML = ''; preview.style.display = 'none'; }
});

// ============== DELETE DOCUMENT FUNCTION ==============

function deleteDocument(documentId) {
    if (confirm('Are you sure you want to delete this document? This action cannot be undone.')) {
        document.getElementById('delete_document_id').value = documentId;
        document.getElementById('deleteDocumentForm').submit();
    }
}

// ============== TABLE SEARCH FUNCTIONALITY WITH PAGINATION ==============

document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase().trim();
    const tableBody = document.getElementById('documentsTableBody');
    const rows = Array.from(tableBody.querySelectorAll('tr.document-row'));

    // Rows are grouped by student via rowspan, so the student-name cell only
    // exists on the first row of each group. We can't filter row-by-row using
    // cell index (it shifts depending on whether the row has that cell), and
    // we can't hide a group's first row independently of its sibling rows or
    // the rowspanned name cell disappears for the rows left visible. Instead,
    // group rows by data-student-id and show/hide the whole group together.
    const groups = new Map();
    rows.forEach(row => {
        const studentId = row.dataset.studentId;
        if (!groups.has(studentId)) groups.set(studentId, []);
        groups.get(studentId).push(row);
    });

    filteredRows = [];
    groups.forEach(groupRows => {
        const studentName = groupRows[0].dataset.studentName || '';
        const groupMatches = !searchTerm ||
            studentName.includes(searchTerm) ||
            groupRows.some(row => (row.dataset.documentType || '').includes(searchTerm));

        if (groupMatches) {
            filteredRows.push(...groupRows);
        }
    });

    // Reset to page 1 when searching
    currentPage = 1;
    updatePagination();
});

// Close search results when clicking outside
document.addEventListener('click', function(event) {
    const addResults = document.getElementById('addStudentResults');
    const editResults = document.getElementById('editStudentResults');
    const addSearch = document.getElementById('addStudentSearch');
    const editSearch = document.getElementById('editStudentSearch');

    if (!addSearch.contains(event.target) && !addResults.contains(event.target)) {
        addResults.style.display = 'none';
    }

    if (!editSearch.contains(event.target) && !editResults.contains(event.target)) {
        editResults.style.display = 'none';
    }
});
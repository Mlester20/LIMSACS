# Copilot Prompt: Search Enrolled Students (Server-Side WHERE LIKE)

## Context

This is a school enrollment system built with PHP (MVC pattern), jQuery, and Bootstrap 5.
The enrolled students table is **server-side paginated** via `getEnrolledStudentsWithPagination()`
in `EnrollmentController.php`. 

The `#searchInput` field on the main page currently has no functionality. The goal is to
implement a **server-side AJAX search** using `WHERE LIKE` so it searches across ALL records,
not just the current page.

The existing `enrollmentController.searchStudent()` in `enrollment.js` is **only** for the
enrollment modal (`#searchStudent`) — do NOT touch it.

---

## Files to Modify

1. `app/controllers/registrar/EnrollmentController.php`
2. `public/js/registrar/enrollment.js`

---

## Task 1 — EnrollmentController.php

Add a new method `searchEnrolledStudents($keyword, $page, $itemsPerPage)` to the
`EnrollmentController` class:

```php
/**
 * Search enrolled students by name or LRN with pagination
 * @param string $keyword
 * @param int $page
 * @param int $itemsPerPage
 * @return array ['enrollments' => array, 'pagination' => array]
 */
public function searchEnrolledStudents($keyword, $page = 1, $itemsPerPage = 10) {
    try {
        $page = max(1, (int)$page);
        $itemsPerPage = max(1, (int)$itemsPerPage);
        $offset = ($page - 1) * $itemsPerPage;
        $searchKeyword = '%' . trim($keyword) . '%';

        // Total count for pagination
        $countQuery = "
            SELECT COUNT(*) as total
            FROM academic_history ah
            JOIN students s ON ah.student_id = s.id
            WHERE ah.enrollment_status = 'Enrolled'
            AND (
                s.first_name LIKE ? OR
                s.last_name LIKE ? OR
                s.lrn LIKE ?
            )
        ";
        $stmt = $this->model->con->prepare($countQuery);
        $stmt->bind_param('sss', $searchKeyword, $searchKeyword, $searchKeyword);
        $stmt->execute();
        $totalRecords = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

        // Paginated results
        $query = "
            SELECT
                ah.id as enrollment_id,
                s.id as student_id,
                s.first_name,
                s.last_name,
                s.lrn,
                sy.school_year,
                ah.grade_level,
                sec.section_name,
                ah.enrollment_status
            FROM academic_history ah
            JOIN students s ON ah.student_id = s.id
            JOIN school_year sy ON ah.school_year_id = sy.id
            JOIN sections sec ON ah.section_id = sec.id
            WHERE ah.enrollment_status = 'Enrolled'
            AND (
                s.first_name LIKE ? OR
                s.last_name LIKE ? OR
                s.lrn LIKE ?
            )
            ORDER BY s.last_name ASC, s.first_name ASC
            LIMIT ? OFFSET ?
        ";
        $stmt = $this->model->con->prepare($query);
        $stmt->bind_param('sssii', $searchKeyword, $searchKeyword, $searchKeyword, $itemsPerPage, $offset);
        $stmt->execute();
        $enrollments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $totalPages = ceil($totalRecords / $itemsPerPage);

        return [
            'enrollments' => $enrollments,
            'pagination' => [
                'currentPage'  => $page,
                'itemsPerPage' => $itemsPerPage,
                'totalRecords' => $totalRecords,
                'totalPages'   => $totalPages,
                'hasPrevPage'  => $page > 1,
                'hasNextPage'  => $page < $totalPages
            ]
        ];
    } catch (Exception $e) {
        error_log("Search enrolled students error: " . $e->getMessage());
        return ['enrollments' => [], 'pagination' => []];
    }
}
```

Then in the **Bootstrap section** (the `if ($_SERVER['REQUEST_METHOD'] === 'POST')` block),
add this new POST handler **before** the closing `}` of the POST block:

```php
// Search enrolled students
if (isset($_POST['search_enrolled'])) {
    $keyword      = $_POST['keyword'] ?? '';
    $page         = $_POST['page'] ?? 1;
    $itemsPerPage = $_POST['items_per_page'] ?? 10;

    $result = $controller->searchEnrolledStudents($keyword, $page, $itemsPerPage);
    header('Content-Type: application/json');
    echo json_encode($result);
    exit();
}
```

---

## Task 2 — enrollment.js

### 2a. Add `searchEnrolled` method to the `enrollmentController` object

Add this method inside the `enrollmentController` object (after `updateCapacityInfo`):

```js
searchEnrolled: function (keyword, page = 1) {
    const tbody = document.querySelector('.table tbody');
    if (!tbody) return;

    // Show loading state
    tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-3">Searching...</td></tr>';

    $.ajax({
        url: '../../../app/controllers/registrar/EnrollmentController.php',
        type: 'POST',
        data: {
            search_enrolled: 1,
            keyword: keyword,
            page: page,
            items_per_page: 10
        },
        dataType: 'json',
        success: function (data) {
            const enrollments = data.enrollments || [];
            const pagination  = data.pagination  || {};
            let html = '';

            if (enrollments.length > 0) {
                enrollments.forEach((enrollment, index) => {
                    const rowNum = (pagination.itemsPerPage * (pagination.currentPage - 1)) + (index + 1);
                    html += `
                        <tr>
                            <td>${rowNum}</td>
                            <td>${enrollment.first_name} ${enrollment.last_name}</td>
                            <td>${enrollment.lrn || 'N/A'}</td>
                            <td>${enrollment.school_year || 'N/A'}</td>
                            <td>${enrollment.grade_level || 'N/A'}</td>
                            <td>${enrollment.section_name || 'N/A'}</td>
                            <td><span class="badge bg-success">${enrollment.enrollment_status}</span></td>
                            <td>
                                <button class="btn btn-sm btn-info" title="View History"
                                    data-bs-toggle="modal"
                                    data-bs-target="#enrollmentHistoryModal"
                                    onclick="enrollmentController.showEnrollmentHistory(${enrollment.student_id}, '${enrollment.first_name} ${enrollment.last_name}')">
                                    <i class="bx bx-history"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
            } else {
                html = '<tr><td colspan="8" class="text-center text-muted py-3">No results found.</td></tr>';
            }

            tbody.innerHTML = html;

            // Update pagination UI
            enrollmentController.updateSearchPagination(keyword, pagination);
        },
        error: function () {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger py-3">Search error. Please try again.</td></tr>';
        }
    });
},

updateSearchPagination: function (keyword, pagination) {
    const paginationEl = document.querySelector('.pagination');
    if (!paginationEl) return;

    if (!keyword || keyword.trim() === '') {
        // Reload the page to restore PHP pagination when search is cleared
        window.location.href = '?page=1';
        return;
    }

    let html = '';

    // Previous
    html += pagination.hasPrevPage
        ? `<li class="page-item"><a class="page-link" href="#" onclick="enrollmentController.searchEnrolled('${keyword}', ${pagination.currentPage - 1}); return false;">Previous</a></li>`
        : `<li class="page-item disabled"><span class="page-link">Previous</span></li>`;

    // Page numbers
    const start = Math.max(1, pagination.currentPage - 2);
    const end   = Math.min(pagination.totalPages, pagination.currentPage + 2);

    for (let i = start; i <= end; i++) {
        html += i === pagination.currentPage
            ? `<li class="page-item active"><span class="page-link">${i}</span></li>`
            : `<li class="page-item"><a class="page-link" href="#" onclick="enrollmentController.searchEnrolled('${keyword}', ${i}); return false;">${i}</a></li>`;
    }

    // Next
    html += pagination.hasNextPage
        ? `<li class="page-item"><a class="page-link" href="#" onclick="enrollmentController.searchEnrolled('${keyword}', ${pagination.currentPage + 1}); return false;">Next</a></li>`
        : `<li class="page-item disabled"><span class="page-link">Next</span></li>`;

    paginationEl.innerHTML = html;
},
```

### 2b. Wire up `#searchInput` inside `DOMContentLoaded`

Replace the existing `#searchInput` keyup listener (the client-side DOM filter) with this
debounced AJAX version:

```js
// Search enrolled students — server-side WHERE LIKE
let searchTimer = null;
document.getElementById('searchInput').addEventListener('keyup', function () {
    const keyword = this.value.trim();
    clearTimeout(searchTimer);

    if (keyword === '') {
        // Restore PHP-rendered table by reloading page
        window.location.href = '?page=1';
        return;
    }

    // Debounce: wait 400ms after user stops typing before firing AJAX
    searchTimer = setTimeout(function () {
        enrollmentController.searchEnrolled(keyword, 1);
    }, 400);
});
```

---

## Constraints

- Do NOT modify `enrollmentController.searchStudent()` — it is for the modal only (`#searchStudent`)
- Do NOT remove existing POST handlers in `EnrollmentController.php`
- The new `searchEnrolledStudents()` method searches `enrollment_status = 'Enrolled'` only
- Use `LIKE` on `first_name`, `last_name`, and `lrn` columns
- Debounce the keyup to avoid firing on every keystroke (400ms delay)
- When search input is cleared, reload `?page=1` to restore PHP pagination

---

## Table Column Reference

| Index | Column       |
|-------|--------------|
| 0     | #            |
| 1     | Student Name |
| 2     | LRN          |
| 3     | School Year  |
| 4     | Grade Level  |
| 5     | Section      |
| 6     | Status       |
| 7     | Actions      |

# Copilot Prompt: Replace Student Dropdown with Search + Duplicate Prevention

## Context

This is a school enrollment system (PHP MVC, jQuery, Bootstrap 5).

The **Add Guardian modal** in `parent-guardians.php` currently has a `<select>` dropdown
that loads ALL students — this is inefficient. Replace it with a **live search input**
(like an autocomplete) that queries students via AJAX.

Additionally, a student should **not be addable** if they already have an existing
guardian record in `parents_guardians` table — prevent duplicates before form submission.

---

## Files to Modify

1. `app/services/StudentsService.php` — add `searchAvailableStudents()` method
2. `app/controllers/registrar/ParentGuardiansController.php` — add POST handler for student search + duplicate check
3. `resources/views/registrar/parent-guardians.php` — replace `<select>` with search input UI
4. `public/js/registrar/parent-guardians.js` — add student search + duplicate check logic

---

## Task 1 — StudentsService.php

Add a new method `searchAvailableStudents($keyword)` to the existing `StudentsService` class.

**Rules:**
- Do NOT add a query directly in the controller — put it here as a service method
- Search by `first_name`, `last_name`, or `lrn` using `LIKE`
- Exclude students who already have a record in `parents_guardians` table
  (use `NOT EXISTS` or `LEFT JOIN ... WHERE pg.id IS NULL`)
- Return max 10 results
- Return `[]` if keyword is empty or less than 2 characters

```php
/**
 * Search students who do NOT yet have a guardian record
 * @param string $keyword
 * @return array
 */
public function searchAvailableStudents($keyword) {
    $keyword = trim($keyword);

    if (empty($keyword) || strlen($keyword) < 2) {
        return [];
    }

    $searchKeyword = '%' . $keyword . '%';

    $query = "
        SELECT s.id, s.lrn, s.first_name, s.middle_name, s.last_name
        FROM students s
        WHERE (
            s.first_name LIKE ? OR
            s.last_name  LIKE ? OR
            s.lrn        LIKE ?
        )
        AND NOT EXISTS (
            SELECT 1 FROM parents_guardians pg WHERE pg.student_id = s.id
        )
        ORDER BY s.first_name ASC, s.last_name ASC
        LIMIT 10
    ";

    try {
        $stmt = $this->con->prepare($query);
        if (!$stmt) return [];

        $stmt->bind_param('sss', $searchKeyword, $searchKeyword, $searchKeyword);
        $stmt->execute();
        $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $results ?? [];
    } catch (Exception $e) {
        error_log("searchAvailableStudents error: " . $e->getMessage());
        return [];
    }
}
```

---

## Task 2 — ParentGuardiansController.php

### 2a. Add `require_once` for StudentsService at the top of the file:

```php
require_once __DIR__ . '/../../services/StudentsService.php';
```

### 2b. Add two new POST handlers inside the existing `if ($_SERVER['REQUEST_METHOD'] === 'POST')` block:

```php
// Search available students (no guardian yet)
if (isset($_POST['search_available_students'])) {
    $keyword = $_POST['keyword'] ?? '';
    $service = new StudentsService($con);
    $results = $service->searchAvailableStudents($keyword);
    header('Content-Type: application/json');
    echo json_encode($results);
    exit();
}

// Check if student already has a guardian record
if (isset($_POST['check_guardian_exists'])) {
    $studentId = (int)($_POST['student_id'] ?? 0);
    $query = "SELECT COUNT(*) as total FROM parents_guardians WHERE student_id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    header('Content-Type: application/json');
    echo json_encode(['exists' => $row['total'] > 0]);
    exit();
}
```

**Important:** These handlers must be placed BEFORE `save_guardian`, `update_guardian`,
and `delete_guardian` checks inside the POST block.

---

## Task 3 — parent-guardians.php

### Replace the student `<select>` inside `#addGuardianModal` with a search input + results dropdown.

Find this block:

```php
<div class="row align-items-center mb-2">
    <label for="student_id" class="col-sm-2 col-form-label col-form-label-sm fw-bold">Student:</label>
    <div class="col-sm-10">
        <select class="form-select form-select-sm" id="student_id" name="student_id" required>
            <option value="" disabled selected>Select a student</option>
            <?php foreach($students as $student): ?>
                <option value="<?php echo $student['id']; ?>">
                    <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
```

Replace it with:

```html
<div class="row align-items-center mb-2">
    <label class="col-sm-2 col-form-label col-form-label-sm fw-bold">Student:</label>
    <div class="col-sm-10">
        <!-- Hidden field — actual value submitted -->
        <input type="hidden" id="add_student_id" name="student_id" required>

        <!-- Search input -->
        <input
            type="text"
            class="form-control form-control-sm"
            id="studentSearchInput"
            placeholder="Search by name or LRN..."
            autocomplete="off"
        >

        <!-- Duplicate warning -->
        <div id="duplicateWarning" class="text-danger small mt-1" style="display:none;">
            ⚠️ This student already has a guardian record.
        </div>

        <!-- Search results dropdown -->
        <div id="studentSearchResults" class="list-group mt-1" style="
            position: absolute;
            z-index: 9999;
            width: calc(100% - 30px);
            max-height: 200px;
            overflow-y: auto;
            display: none;
        "></div>
    </div>
</div>
```

Also **remove** the `$students` variable from the PHP bootstrap block at the top of the
controller include — the `$students = $controller->getStudents();` line and the
`getStudents()` call are no longer needed since we switched to AJAX search.

---

## Task 4 — parent-guardians.js

Add a new section at the bottom (before or after `initializeSearch`) for the student
search inside the Add Guardian modal:

```js
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
    initializeStudentSearch();  // new: student search inside modal
});
```

**Remove** the existing `document.addEventListener('DOMContentLoaded', initializeSearch);`
line at the bottom of the file since it is replaced by the combined listener above.

---

## Constraints

- Do NOT touch `editParentGuardian()`, `viewParentGuardian()`, `renderSearchResults()`,
  `searchGuardians()`, `escapeHtml()`, `escapeJsString()`, or `debounce()` functions
- Do NOT modify the Edit modal — only the Add modal student field changes
- The `save_guardian` POST handler in the controller remains unchanged
- `StudentsService` query must use `NOT EXISTS` to exclude students with existing records
- The submit button inside `#addGuardianModal` must be disabled if a duplicate is detected
- The submit button's `name` attribute must be `save_guardian` (already exists in the form)

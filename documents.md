I have a PHP MVC project. Complete the following files based on the context below.

## PROJECT CONTEXT

### students table columns:
id, lrn, first_name, middle_name, last_name, suffix, gender, birth_date, age,
birth_place, nationality, religion, address, contact_number, created_at

### student_documents table columns:
id, student_id, document_type_id, file_path,
status ENUM('Pending','Submitted','Verified','Rejected') DEFAULT 'Submitted',
remarks, uploaded_by, uploaded_at

### document_types table columns:
id, name (fetched via DocumentTypesModel)

---

## TASK 1 — StudentsDocumentModel.php

Fix the broken index() query (has a stray comma before FROM), and add a find($id) method.

Current broken query:
SELECT sd.*, s.first_name as student_first_name, s.last_name as student_last_name,
FROM student_documents sd ...

Fixed query should also select:
- dt.name as document_type_name
- u.full_name as uploaded_by_name
- sd.uploaded_at

Add this method:
public function find($id) — SELECT single record by id with same JOINs.

Also add a search($keyword) method:
- Search by student LRN (s.lrn LIKE ?) OR student full name (CONCAT(s.first_name, ' ', s.last_name) LIKE ?)
- Same JOIN structure as index()
- Use prepared statement with bind_param

---

## TASK 2 — StudentsDocumentController.php

Complete the create(), update(), and delete() methods.

### Rules:
- DO NOT rewrite or duplicate the StudentsModel search logic. The $this->students property
  already holds StudentsModel. Add a search($keyword) method to the controller that just
  calls $this->students->search($keyword) — delegate, don't repeat.
- Use FileUpload::upload() as a static call (already required at top of file).
- Use FileUpload::delete() for cleanup on update/delete.
- Use $this->auditLogs->log() for audit trail.
- Use flashMessage() for user feedback.
- Use header('Location: ...') + exit for redirects.

### create($data):
- $data keys: student_id, document_type_id, file ($_FILES entry), remarks
- Upload file: FileUpload::upload($data['file'], 'student_documents', "doc_{$data['student_id']}")
- Insert to DB via $this->model->create() with status defaulting to 'Submitted'
- uploaded_by = $_SESSION['user_id']
- Audit log action: 'UPLOAD DOCUMENT', module: 'STUDENT_DOCUMENTS'

### update($id, $data):
- $data keys: student_id, document_type_id, file (optional), status, remarks
- If new file is uploaded (file error === UPLOAD_ERR_OK):
  - fetch existing record via $this->model->find($id)
  - delete old file via FileUpload::delete($existing['file_path'])
  - upload new file, set new file_path
- Update DB via $this->model->update($id, $updateData)
- Audit log action: 'UPDATE DOCUMENT', module: 'STUDENT_DOCUMENTS'

### delete($id):
- fetch record via $this->model->find($id)
- delete file via FileUpload::delete($existing['file_path'])
- delete DB record via $this->model->delete($id)
- Audit log action: 'DELETE DOCUMENT', module: 'STUDENT_DOCUMENTS'

### Add to bootstrap section at the bottom:
Handle POST actions: 'create', 'update', 'delete'
Also handle GET action: 'search' — call $controller->search($_GET['keyword']) and return JSON

---

## TASK 3 — student-documents.php (view)

Uncomment the controller require_once at the top.

Add a search bar above the table:
- Input field: placeholder "Search by LRN or Student Name"
- Triggers AJAX GET to the same page with ?action=search&keyword=...
- Results update the table body dynamically (no full page reload)

Complete the table to display:
| # | LRN | Student Name | Document Type | Status | Uploaded By | Upload Date | Actions |

- Status should be a badge (color-coded: Submitted=blue, Verified=green, Pending=yellow, Rejected=red)
- Actions: Edit button (opens modal), Delete button (POST with confirm)
- Use the existing Bootstrap/jQuery assets already loaded in the file
- Populate table rows from $controller->index() result using PHP foreach
- For the search, use $.get() jQuery AJAX, update tbody with returned HTML rows

---

## EXISTING FILES (do not change their structure, only use them):

### FileUpload (helpers/fileUpload.php) — static class:
- FileUpload::upload($file, $folder, $prefix): string — returns relative path e.g. storage/student_documents/doc_1_xxx.pdf
- FileUpload::delete($relative_path): bool

### StudentsModel — already instantiated as $this->students in the controller.
  Has index() method. You need to ADD search($keyword) to StudentsDocumentModel
  (not StudentsModel) that searches the student_documents JOIN query by LRN or name.
  The controller's search() method just delegates to $this->model->search($keyword).

### AuditLogs — $this->auditLogs->log($userId, $role, $action, $module, $referenceId, $referenceTable, $description)
  (match the existing log() signature already used in audit_logs table)

### flashMessage($type, $message) — helper already required

### Base Controller — parent::__construct($model) sets $this->model
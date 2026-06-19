Let me read all the uploaded files first to fully understand the current codebase.Now I have a thorough understanding of the entire codebase. Here's a well-structured Claude prompt you can use:

---

## The Prompt

```
You are an expert PHP/JavaScript developer. I have an existing school registrar system 
built with a custom MVC framework (PHP + MySQLi + Bootstrap 5 + jQuery). I need you to 
integrate student documents into the Student Profile modal (studentDetailsModal) inside 
student-records.php — so that when a registrar clicks a student row, the modal shows 
their profile, academic history, parent/guardians, AND a Documents tab with a 
checklist of all document_types showing ✓ (submitted/verified) or ✗ (missing) per 
document type.

=== ARCHITECTURE OVERVIEW ===

The app follows this structure:
- Views:  resources/views/registrar/student-records.php
- JS:     public/js/registrar/students.js
- Controller: app/controllers/registrar/StudentsController.php
- Models: app/models/registrar/StudentsModel.php
          app/models/registrar/StudentsDocumentModel.php

The base Controller.php accepts a model via constructor:
  parent::__construct(new SomeModel($con))

The base Model.php wraps a MySQLi $con connection.

=== EXISTING AJAX ENDPOINT ===

StudentsController.php already handles a POST AJAX action:

  if($_POST['action'] === 'get_student_profile'){
      $student_id = intval($_POST['student_id']);
      $profile = $controller->getStudentProfile($student_id);
      echo json_encode($profile);
      exit();
  }

getStudentProfile() currently returns:
  [
    'student'          => [...],   // from students table
    'academic_history' => [...],   // from academic_history table
    'parent_guardians' => [...]    // from parent_guardians table
  ]

The JS function populateStudentModal(student) calls this endpoint via $.ajax POST and
renders academic history and parent/guardians into the modal.

=== WHAT I NEED YOU TO DO ===

**1. PHP — StudentsController.php**
   - In getStudentProfile(), also fetch the student's documents using 
     StudentsDocumentModel. Inject StudentsDocumentModel as a dependency 
     (same pattern as AcademicHistoryModel and ParentGuardiansModel already injected).
   - Also fetch ALL document types from DocumentTypesModel.
   - Return them in the JSON response as two new keys:
       'student_documents' => [...],   // rows from student_documents for this student_id
       'document_types'    => [...]    // all rows from document_types table

   Each student_document row must include at minimum:
     id, student_id, document_type_id, status, remarks, file_path, uploaded_at

   Each document_type row must include:
     id, document_name

**2. PHP — StudentsDocumentModel.php**
   Add a new method:
     public function getByStudentId($student_id)
   
   It should SELECT sd.*, dt.document_name FROM student_documents sd
   LEFT JOIN document_types dt ON sd.document_type_id = dt.id
   WHERE sd.student_id = ?
   ORDER BY dt.document_name ASC

**3. HTML — student-records.php (studentDetailsModal)**
   The current modal has sections for Personal Info, Academic History, and 
   Parent/Guardians side by side. Convert the modal body to use Bootstrap tabs:

   Tab 1: "Profile" — existing personal info, demographics, contact fields 
          (all current #modalXxx divs stay here unchanged)
   Tab 2: "Academic History" — the existing academic history table
   Tab 3: "Parents / Guardians" — the existing parent/guardian cards
   Tab 4: "Documents" — NEW checklist section (see below)

   The Documents tab should render a table or list with columns:
     | Document Type | Status | Remarks | Action |

   - For each document_type in the full list, show one row.
   - If the student HAS that document: show the document's status badge 
     (Submitted = info, Verified = success, Rejected = danger, Pending = warning),
     show remarks, and show a small "View" button that opens file_path in a new tab.
   - If the student DOES NOT HAVE that document: show a red ✗ badge labeled 
     "Missing" and leave Remarks and Action blank.
   - At the top of the Documents tab show a summary line:
       "X of Y documents submitted"  (where Y = total document types)

**4. JavaScript — students.js**
   In populateStudentModal(student), after receiving the AJAX response:
   - Read response.student_documents and response.document_types
   - Call a new function renderDocumentChecklist(studentDocuments, documentTypes)
     that builds the Documents tab HTML described above.
   - The tab structure must use Bootstrap 5 tab syntax 
     (nav-tabs + tab-content + tab-pane).

   renderDocumentChecklist(studentDocs, docTypes):
   - Build a Map keyed by document_type_id from studentDocs for O(1) lookup.
   - Iterate over docTypes to produce a table row per document type.
   - Insert the generated HTML into a container with id="documentChecklistBody".

=== CONSTRAINTS ===
- Keep all existing PHP code untouched except the specific additions above.
- Do not change the AJAX endpoint URL or method — it stays POST to StudentsController.php 
  with action=get_student_profile.
- Use Bootstrap 5 classes only (the project already loads Bootstrap 5 via 
  /public/assets/vendor/js/bootstrap.js).
- Use vanilla JS or jQuery (jQuery is already loaded globally).
- All new PHP must use prepared statements (MySQLi bind_param style, no PDO).
- Do not break the existing tab navigation — the modal's "Edit" button in the footer 
  should still trigger editStudent() for the currently viewed student.
- Maintain the same file structure; don't create new files unless I ask.

=== DELIVERABLES ===
Provide the complete modified versions of these four files (not diffs, full files):
1. app/controllers/registrar/StudentsController.php
2. app/models/registrar/StudentsDocumentModel.php
3. resources/views/registrar/student-records.php  (modal section + tab markup only is 
   fine if the file is large — clearly mark where to replace)
4. public/js/registrar/students.js

For each file, explain in 2–3 sentences what changed and why before showing the code.
```

---

A few notes on why the prompt is written this way:

**Feeds exact context** — it tells Claude the constructor pattern, the existing AJAX response shape, and the jQuery/Bootstrap versions already in use, so it won't invent a different approach.

**Specifies the checklist logic explicitly** — "build a Map keyed by document_type_id" gives Claude a clear algorithm so it doesn't produce a nested-loop O(n²) version.

**Anchors deliverables** — asking for full files (or clearly marked replacement blocks) prevents Claude from returning incomplete snippets that are hard to slot in.

**Constraints prevent breakage** — explicitly forbidding changes to the AJAX URL and the `editStudent()` footer button stops Claude from accidentally refactoring things that already work.
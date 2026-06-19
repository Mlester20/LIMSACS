# LIMSACS — Data Flow Diagram (DFD)

System: School Registrar / Learner Information Management System (LIMSACS)
Scope: Authentication → RBAC → Core Modules → Data Stores → Output
Notation: Gane–Sarson (Process = rounded rectangle, Data Store = open rectangle,
External Entity = rectangle, Data Flow = labeled arrow). Diagrams are written in
Mermaid `flowchart` syntax so they render directly in VS Code / GitHub previews.

---

## 1. Scope & Methodology

This document was derived from the current codebase (no separate router; direct
file-to-controller dispatch). Key facts used as ground truth:

- Front door: `index.php` (login form) → POST → `app/controllers/Auth.php`
- DB connection: `database/config/config.php` → `Database` class → global `mysqli $con`
- Base classes: `app/controllers/Controller.php` (abstract), `app/models/Model.php` (wraps `$con`)
- RBAC guard: `app/middleware/Auth.php` → `AuthRole::allowOnly([...roles])`, called at the
  top of every protected view (`resources/views/{role}/*.php`)
- Cross-cutting helpers: `app/helpers/flashMessage.php` (`FlashMessage`),
  `app/helpers/auditLogs.php` (`AuditLogs`), `app/helpers/password.php` (bcrypt)
- File output: uploaded files under `storage/student_documents/` and `storage/profiles/`
- No PDF/CSV export library is present (no dompdf/mpdf/tcpdf/phpspreadsheet) — output is
  HTML views rendered by controllers + Chart.js dashboards.

---

## 2. External Entities

| Entity | Description |
|---|---|
| **Admin User** | Manages users, school years, audits, system-wide views |
| **Registrar User** | Manages students, enrollment, documents, sections, parents/guardians |
| **Teacher** (`users.role = teacher`) | Assignable as section adviser; currently has no dedicated controller/view set (account exists in RBAC roles but no teacher module found in `app/controllers`) |
| **File System** | `storage/student_documents/`, `storage/profiles/` — receives/serves uploaded files |
| **MySQL Database** (`limsacsdb`) | All persistent data stores (see §6) |

---

## 3. Context Diagram (Level 0)

```mermaid
flowchart TB
    Admin([Admin User])
    Registrar([Registrar User])
    Teacher([Teacher Account])

    System(("LIMSACS\nSchool Registrar System"))

    DB[(limsacsdb\nMySQL)]
    FS[(File Storage\nstorage/)]

    Admin -- "login creds, user/school-year/audit mgmt requests" --> System
    System -- "dashboards, KPI charts, CRUD results, flash messages" --> Admin

    Registrar -- "login creds, student/enrollment/document/section requests" --> System
    System -- "dashboards, student records, document checklists, flash messages" --> Registrar

    Teacher -. "stored as adviser reference only (no active module)" .-> System

    System <--> DB
    System <--> FS
```

---

## 4. Level 1 DFD — Major Processes

```mermaid
flowchart TB
    subgraph Entities
      Admin([Admin User])
      Registrar([Registrar User])
    end

    P1(["1.0\nAuthenticate User"])
    P2(["2.0\nEnforce RBAC\n(AuthRole::allowOnly)"])
    P3(["3.0\nAdmin Module\n(Users / School Year /\nAcademic History / Audit Logs / Dashboard)"])
    P4(["4.0\nRegistrar Module\n(Students / Enrollment / Documents /\nSections / Parents-Guardians / Dashboard)"])
    P5(["5.0\nAudit Logging"])
    P6(["6.0\nFlash Messaging / Output Rendering"])

    DS1[(D1 users)]
    DS2[(D2 school_year)]
    DS3[(D3 students)]
    DS4[(D4 academic_history)]
    DS5[(D5 sections)]
    DS6[(D6 student_documents)]
    DS7[(D7 document_types)]
    DS8[(D8 parents_guardians)]
    DS9[(D9 audit_logs)]
    FS[(File Storage)]

    Admin -- "email/password" --> P1
    Registrar -- "email/password" --> P1
    P1 <--> DS1
    P1 -- "session: id, role,\nfull_name, email,\nprofile_picture" --> P2
    P1 -- "LOGIN event" --> P5

    P2 -- "role-checked request" --> P3
    P2 -- "role-checked request" --> P4
    P2 -- "redirect + flash\n(if unauthorized)" --> P6

    Admin <--> P3
    P3 <--> DS1
    P3 <--> DS2
    P3 <--> DS4
    P3 <--> DS9
    P3 -- "CRUD events" --> P5

    Registrar <--> P4
    P4 <--> DS3
    P4 <--> DS4
    P4 <--> DS5
    P4 <--> DS6
    P4 <--> DS7
    P4 <--> DS8
    P4 <--> FS
    P4 -- "CRUD events" --> P5

    P5 --> DS9
    P3 --> P6
    P4 --> P6
    P6 -- "rendered HTML /\nSweetAlert2 flash" --> Admin
    P6 -- "rendered HTML /\nSweetAlert2 flash" --> Registrar
```

---

## 5. Level 2 DFDs (per process)

### 5.1 Process 1.0 — Authenticate User

```mermaid
flowchart LR
    User([Admin / Registrar User])
    F1["index.php\n(login form)"]
    P11(["1.1\nValidate credentials\nAuth.php::login()"])
    P12(["1.2\nVerify password\nAuthModel::verifyPassword()\n(bcrypt password_verify)"])
    P13(["1.3\nStart session\nAuthController::startUserSession()"])
    P14(["1.4\nRedirect by role\nAuthController::redirectByRole()"])
    DS1[(D1 users)]
    DS9[(D9 audit_logs)]

    User -- "email + password" --> F1
    F1 -- "POST" --> P11
    P11 -- "SELECT by email" --> DS1
    DS1 -- "user row (incl. hash)" --> P11
    P11 --> P12
    P12 -- "valid?" --> P13
    P13 -- "$_SESSION[id, role, full_name,\nemail, profile_picture]" --> P14
    P13 -- "LOGIN, success" --> DS9
    P14 -- "302 redirect to\nrole dashboard" --> User
    P12 -- "invalid: flash 'Invalid email or password'" --> User
```

**Key detail:** password hashing uses `password_hash($password, PASSWORD_BCRYPT, ['cost' => 10])`
(`app/helpers/password.php`); verification is `password_verify()`. No plaintext password is
ever stored or logged.

### 5.2 Process 2.0 — Enforce RBAC

```mermaid
flowchart LR
    Req["Incoming request to a\nprotected view\n(resources/views/{role}/*.php)"]
    P21(["2.1\nisAuthenticated()\nchecks $_SESSION['id']"])
    P22(["2.2\nallowOnly(allowed_roles)\nchecks $_SESSION['role']\nin allowed_roles"])
    P23(["2.3\nDispatch to\ncontroller for view"])
    Flash["FlashMessage::setFlash()"]
    Redirect["302 → index.php"]

    Req --> P21
    P21 -- "not logged in" --> Flash
    Flash --> Redirect
    P21 -- "logged in" --> P22
    P22 -- "role not in allowed list" --> Flash
    P22 -- "role authorized" --> P23
```

**Roles enforced today:** `admin`, `registrar` (every admin view calls
`AuthRole::allowOnly(['admin'])`; every registrar view calls `AuthRole::allowOnly(['registrar'])`).
`teacher` exists as a `users.role` value (assignable as `sections.adviser_id`) but has **no
protected views/controllers of its own** — there is no `allowOnly(['teacher'])` call anywhere
in the codebase.

### 5.3 Process 4.0 — Registrar Module: Student Enrollment & Documents (representative subflow)

```mermaid
flowchart TB
    Registrar([Registrar User])

    P41(["4.1\nSearch / Select Student\nStudentsController"])
    P42(["4.2\nEnroll Student\nEnrollmentController →\nAcademicHistoryModel::create()"])
    P43(["4.3\nUpload Document\nStudentsDocumentController::create()"])
    P44(["4.4\nVerify / Update Document Status\nStudentsDocumentController::update()"])
    P45(["4.5\nManage Parents/Guardians\nParentGuardiansController"])

    DS3[(D3 students)]
    DS4[(D4 academic_history)]
    DS5[(D5 sections)]
    DS6[(D6 student_documents)]
    DS7[(D7 document_types)]
    DS8[(D8 parents_guardians)]
    FS[(File Storage\nstorage/student_documents/)]
    DS9[(D9 audit_logs)]

    Registrar -- "search query" --> P41
    P41 <--> DS3
    P41 -- "student_id" --> P42

    Registrar -- "school_year, grade_level,\nsection_id" --> P42
    P42 <--> DS5
    P42 -- "INSERT enrollment row\n(status, enrolled_by)" --> DS4
    P42 -- "ENROLL STUDENT" --> DS9

    Registrar -- "file + document_type_id" --> P43
    P43 -- "store file" --> FS
    P43 -- "INSERT (status='Submitted')" --> DS6
    P43 <--> DS7
    P43 -- "CREATE DOCUMENT" --> DS9

    Registrar -- "status change\n(Verified/Rejected)" --> P44
    P44 -- "UPDATE status, remarks" --> DS6
    P44 -- "UPDATE DOCUMENT" --> DS9

    Registrar -- "father/mother/guardian info" --> P45
    P45 <--> DS8
```

---

## 6. Data Stores (Data Dictionary)

All access goes through MySQLi **prepared statements** (no PDO; parameterized
`bind_param`) — no raw string interpolation of user input into SQL was found.

| ID | Store | Key Columns | Written by | Read by |
|---|---|---|---|---|
| D1 | `users` | id, full_name, email, password(bcrypt), role, profile_picture, created_at, updated_at | Auth (register/update), Admin UsersController | Auth (login), RBAC middleware, Admin dashboards |
| D2 | `school_year` | id, school_year, start_date, end_date, status(active/inactive/archived) | Admin SchoolYearController | Registrar Enrollment, Sections, Dashboards |
| D3 | `students` | id, lrn, first_name, middle_name, last_name, suffix, gender, birth_date, age, place_of_birth, nationality, religion, address, contact_number | Registrar StudentsController | StudentsController, EnrollmentController, Admin AcademicHistoryController |
| D4 | `academic_history` | id, student_id(FK), enrolled_by(FK→users), school_year_id(FK), grade_level, section_id(FK), enrollment_status | Registrar EnrollmentController | Admin AcademicHistoryController, Registrar/Admin dashboards |
| D5 | `sections` | id, section_name, grade_level, adviser_id(FK→users), school_year_id(FK), max_students | Registrar SectionsController | EnrollmentController |
| D6 | `student_documents` | id, student_id(FK), document_type_id(FK), file_path, status(Pending/Submitted/Verified/Rejected), remarks, uploaded_by(FK→users), uploaded_at | Registrar StudentsDocumentController | StudentsController (profile modal), Admin/Registrar dashboards |
| D7 | `document_types` | id, document_name, is_required, is_active | Registrar DocumentTypesController | StudentsDocumentController |
| D8 | `parents_guardians` | id, student_id(FK), father_*, mother_*, guardian_name, guardian_relationship, guardian_contact, created_at | Registrar ParentGuardiansController | Student profile view |
| D9 | `audit_logs` | id, user_id(FK), role, action, module, reference_id, reference_table, description, ip_address, status(success/failed), created_at | `AuditLogs::log()` (called from nearly every controller mutation) | Admin AuditLogsController |

---

## 7. Output Layer

| Output | Mechanism | Notes |
|---|---|---|
| Role dashboards | Server-rendered HTML + Chart.js | Grade-level distribution, document status pie, registration trend (admin & registrar) |
| Student profile modal | AJAX (`action=get_student_profile`) → JSON → JS render | Includes documents checklist tab (recently added, see git status) |
| Flash notifications | `FlashMessage` (session-based) → SweetAlert2 modal on next page load | Used after every create/update/delete/login |
| Uploaded documents | Served back via `<a href="{file_path}" target="_blank">` from `storage/student_documents/` | No access-control check observed on the static file path itself beyond it being unguessable-by-convention — **flag for review** |
| Audit trail | `audit-logs.php` (admin only) | Read-only table view + delete |
| Exports (PDF/CSV) | **None found** | No dompdf/mpdf/tcpdf/phpspreadsheet dependency in repo |

---

## 8. Notable Gaps / Risks Surfaced While Mapping

These are observations from tracing the flows, not changes made:

1. **Teacher role is structurally a third RBAC role but functionally unused** — no
   `allowOnly(['teacher'])` anywhere, no teacher controllers/views. Either dead scope or
   pending module.
2. **Static file serving for `storage/student_documents/`** is not run through a
   controller/RBAC check in what was traced — if the web server serves that directory
   directly, document URLs are protected only by obscurity, not by `AuthRole`.
3. **No router/front controller** — authorization is opt-in per view (each view must
   remember to call `AuthRole::allowOnly()`); a new view that forgets this call is an
   open page by default, since `index.php` is the only file enforcing the check is fully
   self-policed. This is a defense-in-depth gap worth a lint/checklist rather than a runtime fix.

---

## 9. Source Reference Index

| Concern | File |
|---|---|
| Login entry | `index.php` |
| Auth controller | `app/controllers/Auth.php` |
| Auth model | `app/models/AuthModel.php` |
| Password hashing | `app/helpers/password.php` |
| RBAC middleware | `app/middleware/Auth.php` |
| DB connection | `database/config/config.php` |
| Base Controller/Model | `app/controllers/Controller.php`, `app/models/Model.php` |
| Flash messages | `app/helpers/flashMessage.php` |
| Audit logging | `app/helpers/auditLogs.php` |
| Admin controllers | `app/controllers/admin/*.php` |
| Registrar controllers | `app/controllers/registrar/*.php` |
| Schema reference | `backups/limsacsdb.sql` |

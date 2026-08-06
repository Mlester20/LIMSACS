# Prompt: Implement Excel Import for `students` Master List (MVC)

Copy everything below into Claude Code (or a Claude session with access to this repository).

---

## 0. Read the codebase first — do not write code yet

Before touching anything, explore the project and report back on:

1. **Framework / stack** — plain PHP MVC, CodeIgniter, Laravel, or something else. Check `composer.json`, folder names (`app/Controllers`, `application/controllers`, etc.), and routing files.
2. **Existing MVC conventions** — how are Controllers, Models, and Views named and structured? Show me one existing Controller and one existing Model related to `students` (e.g. student registration/creation) so I can see the pattern: query style (raw SQL / query builder / ORM), how validation is done, how errors/flash messages are returned to views, how routes are registered.
3. **File upload handling** — search for any existing upload feature (e.g. `student_documents` upload in `student_documents` table) and note how it handles file storage, validation, and DB writes. Reuse that pattern rather than introducing a new one.
4. **Auth/role pattern** — how is the logged-in user and role (`admin`, `registrar`, `teacher`) checked in controllers? I need to reuse it (see restrictions below).
5. **Audit logging** — the DB has an `audit_logs` table (`user_id`, `role`, `action`, `module`, `reference_id`, `reference_table`, `description`, `ip_address`, `status`). Find where existing controllers write to it (e.g. on student create/update) and reuse the exact same helper/pattern.
6. **Any existing Excel/CSV handling** — check `composer.json` / `vendor` for PhpSpreadsheet, Laravel Excel, or similar already installed. If none exists, tell me before adding a new dependency, and prefer PhpSpreadsheet (`phpoffice/phpspreadsheet`) unless the codebase already leans on something else.

Only after this review, summarize the pattern you found in 3–5 bullet points, then proceed to implementation using that same pattern.

---

## 1. Scope and constraint (important)

- The `students` table is the **masters list**. This import feature writes **only** to `students`. It must **not** create rows in `academic_history`, `sections`, `parents_guardians`, `graduates`, or `student_documents` — enrollment/section assignment stays a separate, existing workflow.
- `students` schema (from `limsacsdb.sql`):

  | Column | Type | Notes |
  |---|---|---|
  | id | int, PK, auto_increment | never set from Excel |
  | lrn | varchar(20) | nullable in DB, but should be treated as the natural key for duplicate detection |
  | first_name | varchar(100) | |
  | middle_name | varchar(100) | |
  | last_name | varchar(100) | |
  | suffix | varchar(20) | |
  | gender | enum('Male','Female') | |
  | birth_date | date | |
  | age | int | |
  | place_of_birth | varchar(150) | |
  | nationality | varchar(100) | |
  | religion | varchar(100) | |
  | address | text | |
  | contact_number | varchar(20) | |
  | created_at | timestamp | default current_timestamp, don't set from Excel |

- **Note the DB gap**: `lrn` has no `UNIQUE` index in the current schema. Either:
  - (a) add a migration/SQL `ALTER TABLE students ADD UNIQUE KEY uq_lrn (lrn);` if the codebase supports migrations, **or**
  - (b) if altering the schema is out of scope, enforce uniqueness in the Model layer with a `SELECT` check before insert.

  Ask me which approach fits before applying (a), since it changes the live schema.

---

## 2. Functional requirements

**Controller** (e.g. `StudentImportController` — match existing naming convention found in step 0):
- `showImportForm()` — renders the upload view.
- `import()` — handles the POST, delegates parsing/validation to the Model/service layer, and renders a results view.
- Restrict access to roles that currently manage `students` (check how existing student-create endpoints restrict roles — likely `admin` and `registrar`; do not open this to `teacher`).

**Model** (e.g. `StudentModel` — extend/reuse whatever model already handles `students`):
- `importFromFile($filePath)` (or per-row `bulkInsert(array $rows)`), returning a structured result: counts of inserted / skipped / failed rows, plus per-row error detail.
- Row-level validation before insert:
  - Required: `first_name`, `last_name`, `gender` (must be `Male`/`Female` — case-insensitive match, normalize before insert), `birth_date` (must parse to a valid date).
  - `lrn`, if present, must be unique against existing `students.lrn` **and** unique within the same uploaded file (catch duplicate rows in one file too).
  - Trim all string fields; treat empty-string cells as `NULL` for nullable columns.
  - `age`, if left blank in the sheet, should be computed from `birth_date` (same logic used elsewhere in the codebase if it already computes age — reuse it, don't duplicate).
- Wrap the whole import in a single DB transaction **per batch**, but track row-level success/failure so one bad row doesn't necessarily roll back the whole file — confirm with existing pattern (some systems do all-or-nothing; check if there's an existing bulk-insert feature and mirror its transaction behavior rather than picking arbitrarily).
- Insert only, never update — this is a masters-list *import*, not a sync. If `lrn` already exists, mark that row as "skipped: duplicate LRN" rather than overwriting.
- Write one `audit_logs` entry summarizing the batch (e.g. `action = 'IMPORT STUDENTS'`, `module = 'STUDENTS'`, `reference_table = 'students'`, `description` = "X inserted, Y skipped, Z failed"), using whatever audit-log helper already exists.

**View**:
- Upload form: single file input (`.xlsx`/`.csv`), matching existing form styling/markup conventions found in step 0.
- A downloadable template link/button (a blank `.xlsx` with the exact header row: `lrn, first_name, middle_name, last_name, suffix, gender, birth_date, age, place_of_birth, nationality, religion, address, contact_number`).
- Results view/partial: total rows processed, inserted count, skipped count (with reason, e.g. duplicate LRN), failed count (with validation error per row/line number). Match existing flash-message/table styling.

**Routes**:
- Add GET route for the upload form and POST route for the import action, registered the same way existing student routes are registered (same file, same middleware/auth wrapper).

---

## 2a. Historical / already-graduated students — explicitly out of scope

Some rows in the Excel file may represent students from past school years who have **already graduated**. This import feature must **not** attempt to infer or create graduation status. Confirmed decision:

- This import writes **only** to `students`. It never creates or touches `academic_history` or `graduates` rows, even for students who are known to be past graduates.
- Do **not** add "graduation date," "school year," or "honors" columns to the import template. Keep the template exactly as listed in Section 1.
- After a bulk import, any student who needs to be marked as `Enrolled`/`Graduated`/etc. is handled **one at a time** through the existing enrollment/graduation feature already in the codebase (the one that writes to `academic_history` and, on graduation, to `graduates`). Find that existing controller/model in step 0 and do not duplicate its logic here.
- **UX nicety (small, optional):** on the import results view, after a successful batch, show a note such as: *"Students imported to the masters list. To record enrollment or graduation history for these students, use the Enrollment page."* — with a link to that existing page if one exists. This is just a pointer, not a functional change to this feature.

If a later ticket asks for bulk historical enrollment/graduation import, that should be a **separate** feature/prompt built on top of the existing single-record enrollment workflow — not bolted onto this one.

---

## 3. Non-functional requirements

- Validate file type/extension and a reasonable max file size before parsing.
- Never trust the Excel header order — map columns by header name, not position, so re-ordered templates still work. Do a case-insensitive header match.
- Sanitize all string input the same way existing student-creation code does (reuse existing sanitization/escaping helpers — don't introduce a second sanitization method).
- Handle malformed files (corrupt upload, wrong extension, empty file) gracefully with a user-facing error, not a stack trace.
- Log failures to your existing error/log mechanism if one exists (separate from `audit_logs`, which is for successful business actions).

---

## 4. Deliverables checklist

- [ ] Migration/SQL note (or actual migration) for the `lrn` uniqueness decision from Section 1 — **ask before applying**
- [ ] Controller method(s) for form + import, following existing naming/auth pattern
- [ ] Model method(s) for parsing, validating, and bulk-inserting into `students` only
- [ ] Upload view + results view/partial, matching existing UI conventions
- [ ] Route registration
- [ ] Downloadable `.xlsx` template matching the `students` column list above
- [ ] Audit log entry on successful import, using the existing helper
- [ ] Short summary at the end of what was added/changed and any manual step needed (e.g. running `composer require phpoffice/phpspreadsheet` if not already present)

Work through this in the order above, and pause to ask me if anything in the existing codebase conflicts with a requirement here (e.g. if models already do updates-on-conflict elsewhere and you're unsure whether to follow that instead of insert-only).
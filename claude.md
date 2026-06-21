# Create Admin Graduates Module (PHP MVC + MySQL)

Analyze the existing School Records Management System database and architecture.

Create a complete **Admin Graduates Module** using PHP MVC architecture.

## Objective

Provide a centralized Graduate Management System where administrators can:

* View all graduated students.
* Access a master list of graduates.
* Filter graduates by school year, grade level, section, and graduation status.
* Search graduates by LRN or student name.
* View complete academic history.
* Export graduate records.
* Generate graduate statistics and analytics.

---

## Architecture Requirements

Follow MVC architecture.

### Model

Create:

```text
app/models/admin/GraduatesModel.php
```

Responsibilities:

* Retrieve graduate records.
* Retrieve graduate statistics.
* Retrieve academic history of graduates.
* Retrieve graduates by school year.
* Retrieve graduates by section.
* Retrieve graduates by grade level.
* Search graduates.
* Retrieve graduate counts.

Use:

```php
require_once __DIR__ . '/../Model.php';
```

Use:

```php
$this->con
```

as the mysqli connection.

Use prepared statements for all queries.

---

### Controller

Create:

```text
app/controllers/admin/GraduatesController.php
```

Responsibilities:

* Display graduate dashboard.
* Display graduate master list.
* Display graduate profile.
* Display academic history.
* Handle filters.
* Handle searches.
* Handle exports.

---

### Views

Create:

```text
resources/views/admin/graduates/index.php
resources/views/admin/graduates/view.php
resources/views/admin/graduates/master-list.php
```

Use Bootstrap 5.

Follow the same UI style used by the existing Admin Dashboard.

---

## Graduate Source

Use the existing database structure.

Determine graduates from:

```text
academic_history
```

or any equivalent academic status table.

Graduated students should be identified through:

```text
enrollment_status = 'Completed'
```

or

```text
student_status = 'Graduated'
```

depending on the actual schema.

Do NOT create a separate graduates table if the data already exists in academic history.

---

## Graduate Dashboard

Create summary cards:

```text
Total Graduates
Male Graduates
Female Graduates
Current School Year Graduates
Previous School Year Graduates
```

Create analytics:

```text
Graduates Per School Year
Graduates Per Grade Level
Graduates Per Section
```

Provide chart-ready datasets.

---

## Master List of Graduates

Create a data table containing:

```text
LRN
Student Name
Gender
Grade Level
Section
School Year
Date Graduated
Registrar
Status
```

Features:

* Pagination
* Search
* Sorting
* Filters
* Export buttons

Filters:

```text
School Year
Grade Level
Section
Gender
Status
```

---

## Graduate Profile

Create a detailed graduate profile page.

Display:

```text
Personal Information
Academic History
Enrollment History
Section History
Graduation Information
Documents Submitted
```

---

## Academic History Integration

Display all academic records for the selected graduate:

```text
School Year
Grade Level
Section
Enrollment Status
Date Enrolled
Date Completed
```

Provide a timeline view.

---

## Export Features

Prepare controller and service methods for:

```text
Export PDF
Export Excel
Print Master List
```

Create method stubs if implementation libraries are not yet installed.

---

## Performance Requirements

Avoid N+1 queries.

Use JOINs where appropriate.

Create aggregate queries for dashboard statistics.

Do not perform one query per graduate.

Implement a DashboardStatsService if needed.

---

## Security Requirements

Use:

* Prepared statements
* Session validation
* Role validation
* CSRF protection for POST actions
* Output escaping

Only users with:

```text
admin
```

role may access this module.

---

## Deliverables

Generate:

1. GraduatesModel.php
2. GraduatesController.php
3. Graduate dashboard view
4. Master list view
5. Graduate profile view
6. SQL queries
7. Routing examples
8. Suggested folder structure
9. Bootstrap UI layout
10. Performance optimization recommendations

Base all implementation on the existing database schema and avoid creating redundant tables.

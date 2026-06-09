# Transferee and Transfer-Out Management Module

## Overview

The Transfer Management Module handles incoming transferees and outgoing transfer students while preserving student records and academic history.

The module prevents duplication of student records and ensures that transfer transactions are properly documented within the enrollment system.

---

# Objectives

* Allow the Registrar to process incoming transferees.
* Allow the Registrar to process transfer-out requests.
* Maintain a complete academic history of students.
* Prevent duplicate student records.
* Track transfer transactions.
* Support future enrollment and reporting requirements.

---

# Business Rules

## Incoming Transferee

A student coming from another school.

```text
Previous School
        ↓
Registrar Creates/Finds Student Record
        ↓
Upload Required Documents
        ↓
Assign Grade Level and Section
        ↓
Create Academic History
        ↓
Enrollment Type = Transferee
```

---

## Transfer-Out Student

A currently enrolled student requesting transfer to another school.

```text
Currently Enrolled
        ↓
Transfer Request
        ↓
Registrar Processes Request
        ↓
Academic History Updated
        ↓
Enrollment Status = Transferred
```

---

# Database Design

## academic_history

Stores enrollment and transfer records.

| Field             | Type         | Description                |
| ----------------- | ------------ | -------------------------- |
| id                | INT (PK)     | Academic history record    |
| student_id        | INT (FK)     | Student reference          |
| school_year_id    | INT (FK)     | School year                |
| grade_level       | VARCHAR(50)  | Grade level                |
| section_id        | INT (FK)     | Assigned section           |
| enrollment_type   | ENUM         | Type of enrollment         |
| enrollment_status | ENUM         | Current status             |
| previous_school   | VARCHAR(255) | Previous school (optional) |
| transfer_date     | DATE         | Transfer date              |
| created_at        | TIMESTAMP    | Record creation            |

---

## Enrollment Types

```text
New
Regular
Returnee
Transferee
```

---

## Enrollment Status

```text
Enrolled
Completed
Graduated
Transferred
Inactive
```

---

# Entity Relationship Diagram (ERD)

```text
+-----------+
| students  |
+-----------+
| id (PK)   |
+-----------+
      |
      | 1
      |
      | M
+----------------------+
| academic_history     |
+----------------------+
| id (PK)              |
| student_id (FK)      |
| school_year_id (FK)  |
| section_id (FK)      |
| enrollment_type      |
| enrollment_status    |
| previous_school      |
+----------------------+
      |
      | M
      |
      | 1
+------------------+
| school_year      |
+------------------+

+------------------+
| sections         |
+------------------+
```

---

# Data Flow Diagram (DFD)

## Context Diagram (Level 0)

```text
+----------------+
|   Registrar    |
+----------------+
        |
        | Process Transfer
        v
+--------------------------------+
| Transfer Management System     |
+--------------------------------+
        |
        | Store / Retrieve Data
        v
+------------------------+
|       Database         |
+------------------------+
```

---

# Level 1 DFD

## Process 1 – Register Transferee

```text
Registrar
      |
      | Create Student Record
      v
+----------------------+
| Register Transferee  |
+----------------------+
      |
      +-----> students
      |
      +-----> student_documents
      |
      v
Transferee Profile
```

### Description

Creates a permanent student profile and stores submitted transfer requirements.

---

## Process 2 – Enroll Transferee

```text
Registrar
      |
      | Enroll Student
      v
+----------------------+
| Enroll Transferee    |
+----------------------+
      |
      +-----> school_year
      |
      +-----> sections
      |
      v
+----------------------+
| academic_history     |
+----------------------+
```

### Result

```text
Enrollment Type:
Transferee

Enrollment Status:
Enrolled
```

---

## Process 3 – Process Transfer-Out

```text
Registrar
      |
      | Transfer Student
      v
+----------------------+
| Transfer Out Student |
+----------------------+
      |
      v
+----------------------+
| academic_history     |
+----------------------+
```

### Result

```text
Enrollment Status:
Transferred
```

---

## Process 4 – View Transfer History

```text
Registrar
      |
      | View Student
      v
+----------------------+
| View Transfer Record |
+----------------------+
      |
      +-----> students
      |
      +-----> academic_history
      |
      v
Transfer History
```

---

# System Architecture

```text
+------------------------------------------------+
|             Presentation Layer                 |
|------------------------------------------------|
| Transferee Registration Form                   |
| Transfer Out Form                              |
| Student Profile Page                           |
| Academic History Page                          |
+------------------------------------------------+
                    |
                    v
+------------------------------------------------+
|              Application Layer                 |
|------------------------------------------------|
| Enrollment Controller                          |
| Transfer Management Controller                 |
| Student Validation Service                     |
| Document Verification Service                  |
| Audit Logging Service                          |
+------------------------------------------------+
                    |
                    v
+------------------------------------------------+
|                  Data Layer                    |
|------------------------------------------------|
| students                                       |
| academic_history                               |
| school_year                                    |
| sections                                       |
| student_documents                              |
| audit_logs                                     |
+------------------------------------------------+
```

---

# Incoming Transferee Workflow

```text
Transferee Arrives
        ↓
Registrar Searches Existing Student
        ↓
Student Found?
      /     \
    Yes      No
     ↓        ↓
Use Existing  Create Student
Record         Profile
     ↓             ↓
Upload Documents
        ↓
Assign Grade Level
        ↓
Assign Section
        ↓
Create Academic History
        ↓
Enrollment Type = Transferee
        ↓
Enrollment Status = Enrolled
```

---

# Transfer-Out Workflow

```text
Student Requests Transfer
            ↓
Registrar Opens Student Profile
            ↓
Verify Current Enrollment
            ↓
Process Transfer Request
            ↓
Update Academic History
            ↓
Enrollment Status = Transferred
            ↓
Generate Documents
            ↓
Create Audit Log
```

---

# Audit Logging

All transfer actions should be recorded.

### Examples

```text
Registered Transferee:
Juan Dela Cruz

Processed Transfer-Out:
Maria Santos

Updated Academic History:
Student #10025
```

Stored in:

```text
audit_logs
```

---

# Benefits

* Prevents duplicate student records.
* Maintains complete academic history.
* Supports incoming and outgoing transfers.
* Preserves enrollment records.
* Supports audit logging.
* Follows database normalization principles.
* Integrates with enrollment and student document management.
* Scalable for future registrar operations.

```
```


CREATE TABLE academic_history (
    id INT AUTO_INCREMENT PRIMARY KEY,

    student_id INT NOT NULL,
    school_year_id INT NOT NULL,
    grade_level VARCHAR(50) NOT NULL,
    section_id INT NOT NULL,

    enrollment_type ENUM(
        'New',
        'Regular',
        'Returnee',
        'Transferee'
    ) DEFAULT 'Regular',

    enrollment_status ENUM(
        'Enrolled',
        'Completed',
        'Graduated',
        'Transferred',
        'Inactive'
    ) DEFAULT 'Enrolled',

    previous_school VARCHAR(255) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (student_id)
        REFERENCES students(id),

    FOREIGN KEY (school_year_id)
        REFERENCES school_year(id),

    FOREIGN KEY (section_id)
        REFERENCES sections(id)
);
# Student Enrollment and Graduation Architecture

## Overview

The system follows a centralized student record architecture where all student information remains in the `students` table throughout the student's lifecycle. Historical enrollment records are stored in `academic_history`, while graduation-specific records are stored in a dedicated `graduates` table.

This design prevents data duplication and maintains a complete audit trail.

---

# Database Architecture

## Users

Stores system accounts including registrars.

```text
users
├── id (PK)
├── username
├── password
├── role
├── first_name
├── last_name
├── status
├── created_at
└── updated_at
```

---

## Students

Stores the master student record.

```text
students
├── id (PK)
├── lrn
├── first_name
├── middle_name
├── last_name
├── gender
├── birth_date
├── address
├── contact_number
├── status
├── created_at
└── updated_at
```

### Student Status Values

* Active
* Graduated
* Transferred
* Dropped
* Inactive

---

## Academic History

Stores enrollment history for every school year.

```text
academic_history
├── id (PK)
├── student_id (FK → students.id)
├── school_year_id (FK)
├── grade_level_id (FK)
├── section_id (FK)
├── enrolled_by (FK → users.id)
├── enrollment_status
├── created_at
└── updated_at
```

### Enrollment Status Values

* Enrolled
* Promoted
* Retained
* Graduated
* Transferred
* Dropped

---

## Student Documents

Stores uploaded student requirements and supporting documents.

```text
student_documents
├── id (PK)
├── student_id (FK → students.id)
├── document_type_id (FK)
├── file_path
├── status
├── remarks
├── uploaded_by (FK → users.id)
└── uploaded_at
```

---

## Parent / Guardian Records

Stores guardian information.

```text
parent_guardians
├── id (PK)
├── student_id (FK → students.id)
├── guardian_name
├── relationship
├── contact_number
└── address
```

---

## Graduates

Stores graduation-specific information.

```text
graduates
├── id (PK)
├── student_id (FK → students.id)
├── academic_history_id (FK → academic_history.id)
├── graduation_date
├── honors
├── remarks
├── recorded_by (FK → users.id)
└── created_at
```

---

# Entity Relationship Flow

```text
users
  │
  ├── enrolled_by
  ▼
academic_history
  ▲
  │
students
  │
  ├── student_documents
  │
  ├── parent_guardians
  │
  └── graduates
```

---

# Enrollment Process

1. Registrar creates or selects a student.
2. Registrar uploads required documents.
3. Registrar assigns:

   * School Year
   * Grade Level
   * Section
4. A new record is inserted into `academic_history`.
5. `enrolled_by` stores the registrar responsible for the enrollment.

---

# Promotion Process

At the end of the school year:

1. Student is promoted to the next grade level.
2. A new academic history record is created.
3. Previous records remain unchanged for historical tracking.

---

# Graduation Process

When a student completes the final grade level:

1. Academic history status becomes `Graduated`.
2. Student status becomes `Graduated`.
3. A record is inserted into the `graduates` table.
4. Graduation information becomes available for reports and transcripts.

---

# Advantages

* No duplicate student records.
* Complete enrollment history.
* Tracks which registrar performed enrollment.
* Supports transcript generation.
* Supports graduate reports.
* Supports future alumni modules.
* Maintains audit trail for enrollment activities.
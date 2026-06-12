# Student Document Management Module

## Overview

The Student Document Management Module allows the Registrar to manage, upload, verify, and track student requirements during enrollment.

Instead of creating separate columns for every requirement, the system uses a flexible design where document requirements are stored in a master table and student submissions are stored in a transaction table.

This approach allows the school to add new document requirements without changing the database structure.

---

# Objectives

* Allow the Registrar to create document requirements.
* Allow the Registrar to upload student documents.
* Track document submission status.
* Verify or reject submitted documents.
* Generate a checklist of student requirements.
* Support future document requirements without database modifications.

---

# Database Design

## document_types

Stores all possible document requirements.

| Field         | Type         | Description                           |
| ------------- | ------------ | ------------------------------------- |
| id            | INT (PK)     | Unique document type ID               |
| document_name | VARCHAR(100) | Name of requirement                   |
| is_required   | BOOLEAN      | Indicates if requirement is mandatory |

### Example Records

| id | document_name          |
| -- | ---------------------- |
| 1  | Birth Certificate      |
| 2  | Form 137               |
| 3  | Good Moral Certificate |
| 4  | Medical Certificate    |
| 5  | Report Card            |

---

## student_documents

Stores uploaded student documents.

| Field            | Type         | Description            |
| ---------------- | ------------ | ---------------------- |
| id               | INT (PK)     | Unique document record |
| student_id       | INT (FK)     | Student owner          |
| document_type_id | INT (FK)     | Required document type |
| file_path        | VARCHAR(500) | Uploaded file location |
| status           | ENUM         | Document status        |
| remarks          | TEXT         | Registrar remarks      |
| uploaded_by      | INT (FK)     | Registrar/User ID      |
| uploaded_at      | TIMESTAMP    | Upload timestamp       |

### Status Values

* Pending
* Submitted
* Verified
* Rejected

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
| student_documents    |
+----------------------+
| id (PK)              |
| student_id (FK)      |
| document_type_id(FK) |
| file_path            |
| status               |
| uploaded_by          |
+----------------------+
      |
      | M
      |
      | 1
+------------------+
| document_types   |
+------------------+
| id (PK)          |
| document_name    |
+------------------+

+-----------+
| users     |
+-----------+
| id (PK)   |
+-----------+
      |
      | 1
      |
      | M
+----------------------+
| student_documents    |
+----------------------+
```

---

# Data Flow Diagram (DFD)

## Context Diagram (Level 0)

```text
+-----------+                    +------------------+
| Registrar |                    |     Student      |
+-----------+                    +------------------+
      |                                   |
      | Upload / Verify Documents         |
      |---------------------------------->|
      |                                   |
      v                                   |
+----------------------------------------------+
|        Student Document Management System    |
+----------------------------------------------+
      |
      | Store / Retrieve Documents
      v
+--------------------+
|     Database       |
+--------------------+
```

### Description

The Registrar interacts with the system to upload and verify student documents.

The system stores all document information in the database and retrieves records when needed.

---

## Level 1 DFD

### Process 1 - Manage Document Types

```text
Registrar
    |
    | Add/Edit/Delete Requirement
    v
+----------------------+
| Manage Document Type |
+----------------------+
    |
    v
+----------------------+
|    document_types    |
+----------------------+
```

### Process 2 - Upload Student Documents

```text
Registrar
    |
    | Upload File
    v
+----------------------+
| Upload Document      |
+----------------------+
      |
      +------> students
      |
      v
+----------------------+
| student_documents    |
+----------------------+
```

### Process 3 - Verify Documents

```text
Registrar
    |
    | Verify / Reject
    v
+----------------------+
| Verify Document      |
+----------------------+
      |
      v
+----------------------+
| student_documents    |
+----------------------+
```

### Process 4 - Check Student Requirements

```text
Registrar
    |
    | View Student
    v
+----------------------+
| Check Requirements   |
+----------------------+
      |
      +------> students
      |
      +------> document_types
      |
      +------> student_documents
      |
      v
 Requirement Status
```

---

# System Architecture

```text
+------------------------------------------------+
|                Presentation Layer              |
|------------------------------------------------|
| Registrar Dashboard                            |
| Student Profile Page                           |
| Document Upload Form                           |
| Requirement Checklist                          |
+------------------------------------------------+
                     |
                     v
+------------------------------------------------+
|                 Application Layer              |
|------------------------------------------------|
| Authentication Module                          |
| Student Management Module                      |
| Document Management Module                     |
| Verification Module                            |
| Audit Logging Module                           |
+------------------------------------------------+
                     |
                     v
+------------------------------------------------+
|                    Data Layer                  |
|------------------------------------------------|
| users                                          |
| students                                       |
| document_types                                |
| student_documents                             |
| audit_logs                                    |
+------------------------------------------------+
```

---

# Enrollment Workflow

```text
1. Registrar creates document requirements
                    ↓
2. Student record is created
                    ↓
3. Registrar uploads submitted documents
                    ↓
4. System stores records in student_documents
                    ↓
5. Registrar verifies or rejects documents
                    ↓
6. Status is updated
                    ↓
7. System checks completion of requirements
                    ↓
8. Student enrollment requirements become complete
```

---

# Example Scenario

Student: Juan Dela Cruz

Required Documents:

* Birth Certificate
* Form 137
* Good Moral Certificate
* Medical Certificate

Current Status:

| Requirement            | Status    |
| ---------------------- | --------- |
| Birth Certificate      | Verified  |
| Form 137               | Verified  |
| Good Moral Certificate | Missing   |
| Medical Certificate    | Submitted |

Result:

The student cannot be tagged as "Requirements Complete" until all required documents are verified.

---

# Benefits of the Design

* Flexible and scalable.
* Supports future document requirements.
* Easy verification process.
* Tracks upload history.
* Supports audit logging.
* Follows database normalization principles.
* Minimizes future database changes.
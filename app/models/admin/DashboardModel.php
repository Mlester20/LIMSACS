<?php
require_once __DIR__ . '/../../models/Model.php'; 

    class DashboardModel extends Model{
        public function __construct($con)
        {
            parent::__construct($con);
        }

        // ── Stat counters ──────────────────────────────────────────────────────────

        public function getTotalStudents(): int
        {
            $row = $this->con->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc();
            return (int) ($row['total'] ?? 0);
        }

        public function getTotalSections(): int
        {
            $row = $this->con->query("SELECT COUNT(*) AS total FROM sections")->fetch_assoc();
            return (int) ($row['total'] ?? 0);
        }

        public function getTotalActiveSchoolYears(): int
        {
            $row = $this->con->query(
                "SELECT COUNT(*) AS total FROM school_year WHERE status = 'active'"
            )->fetch_assoc();
            return (int) ($row['total'] ?? 0);
        }

        public function getTotalRequiredDocuments(): int
        {
            $row = $this->con->query(
                "SELECT COUNT(*) AS total FROM document_types WHERE is_required = 1 AND is_active = 1"
            )->fetch_assoc();
            return (int) ($row['total'] ?? 0);
        }

        public function getPendingDocumentsCount(): int
        {
            $row = $this->con->query(
                "SELECT COUNT(*) AS total FROM student_documents WHERE status = 'Pending'"
            )->fetch_assoc();
            return (int) ($row['total'] ?? 0);
        }

        public function getVerifiedDocumentsCount(): int
        {
            $row = $this->con->query(
                "SELECT COUNT(*) AS total FROM student_documents WHERE status = 'Verified'"
            )->fetch_assoc();
            return (int) ($row['total'] ?? 0);
        }

        public function getRejectedDocumentsCount(): int
        {
            $row = $this->con->query(
                "SELECT COUNT(*) AS total FROM student_documents WHERE status = 'Rejected'"
            )->fetch_assoc();
            return (int) ($row['total'] ?? 0);
        }

        public function getCurrentEnrolledStudents(): int
        {
            $row = $this->con->query("
                SELECT COUNT(*) AS total
                FROM   academic_history ah
                INNER JOIN school_year sy ON sy.id = ah.school_year_id
                WHERE  sy.status            = 'active'
                AND  ah.enrollment_status = 'Enrolled'
            ")->fetch_assoc();
            return (int) ($row['total'] ?? 0);
        }

        // ── Detail tables ──────────────────────────────────────────────────────────

        public function getRecentStudentRegistrations(): array
        {
            $result = $this->con->query("
                SELECT lrn,
                    CONCAT(first_name, ' ', last_name) AS full_name,
                    gender,
                    contact_number,
                    created_at
                FROM   students
                ORDER  BY created_at DESC
                LIMIT  10
            ");
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        }

        public function getRecentDocumentUploads(): array
        {
            // Column is `document_name`, not `name` — confirmed from schema.
            $result = $this->con->query("
                SELECT CONCAT(s.first_name, ' ', s.last_name) AS student_name,
                    dt.document_name,
                    sd.status,
                    sd.uploaded_at
                FROM   student_documents sd
                INNER JOIN document_types dt ON dt.id = sd.document_type_id
                INNER JOIN students        s  ON s.id  = sd.student_id
                ORDER  BY sd.uploaded_at DESC
                LIMIT  10
            ");
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        }

        public function getGradeLevelSummary(): array
        {
            $result = $this->con->query("
                SELECT grade_level,
                    COUNT(*) AS total_students
                FROM   academic_history
                GROUP  BY grade_level
                ORDER  BY grade_level ASC
            ");
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        }

        public function getDocumentStatusSummary(): array
        {
            $result = $this->con->query("
                SELECT status,
                    COUNT(*) AS total
                FROM   student_documents
                GROUP  BY status
            ");
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        }
    }
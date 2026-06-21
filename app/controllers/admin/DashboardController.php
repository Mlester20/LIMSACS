<?php
require_once __DIR__ . '/../../../database/config/config.php';
require_once __DIR__ . '/../../models/admin/DashboardModel.php';

class DashboardController
{
    private DashboardModel $dashboardModel;

    public function __construct($con)
    {
        $this->dashboardModel = new DashboardModel($con);
    }

    public function index(): array
    {
        $documentStatusSummary = $this->dashboardModel->getDocumentStatusSummary();
        $documentStatusCounts  = array_column($documentStatusSummary, 'total', 'status');

        return [
            'total_students'          => $this->dashboardModel->getTotalStudents(),
            'enrolled_students'       => $this->dashboardModel->getCurrentEnrolledStudents(),
            'total_sections'          => $this->dashboardModel->getTotalSections(),
            'active_school_years'     => $this->dashboardModel->getTotalActiveSchoolYears(),
            'required_documents'      => $this->dashboardModel->getTotalRequiredDocuments(),
            'pending_documents'       => (int) ($documentStatusCounts['Pending'] ?? 0),
            'verified_documents'      => (int) ($documentStatusCounts['Verified'] ?? 0),
            'rejected_documents'      => (int) ($documentStatusCounts['Rejected'] ?? 0),
            'recent_registrations'    => $this->dashboardModel->getRecentStudentRegistrations(),
            'recent_uploads'          => $this->dashboardModel->getRecentDocumentUploads(),
            'grade_level_summary'     => $this->dashboardModel->getGradeLevelSummary(),
            'document_status_summary' => $documentStatusSummary,
            'registration_trend'        => $this->dashboardModel->getMonthlyRegistrationTrend(),
            'enrollment_status_summary' => $this->dashboardModel->getEnrollmentStatusBreakdown(),
            'total_graduates'           => $this->dashboardModel->getTotalGraduates(),
            'graduates_active_year'     => $this->dashboardModel->getGraduatesActiveSchoolYear(),
            'section_capacity'          => $this->dashboardModel->getSectionCapacityUtilization(),
        ];
    }
}

// ── Bootstrap ─────────────────────────────────────────────────────────────────
try {
    $controller = new DashboardController($con);
    $data       = $controller->index();
} catch (Throwable $dashboardException) {
    error_log('[DashboardController] ' . $dashboardException->getMessage()
        . ' in ' . $dashboardException->getFile()
        . ' on line ' . $dashboardException->getLine());
    $data = array_fill_keys([
        'total_students', 'enrolled_students', 'total_sections', 'active_school_years',
        'required_documents', 'pending_documents', 'verified_documents', 'rejected_documents',
        'recent_registrations', 'recent_uploads', 'grade_level_summary', 'document_status_summary',
        'registration_trend', 'enrollment_status_summary', 'total_graduates',
        'graduates_active_year', 'section_capacity',
    ], null);
    $data['error'] = 'Dashboard data could not be loaded. Please try again.';
}
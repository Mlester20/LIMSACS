<?php
require_once __DIR__ . '/../../../database/config/config.php';
require_once __DIR__ . '/../../models/registrar/DashboardModel.php';

class DashboardController
{
    private DashboardModel $dashboardModel;

    public function __construct($con)
    {
        $this->dashboardModel = new DashboardModel($con);
    }

    public function index(): array
    {
        return [
            'total_students'          => $this->dashboardModel->getTotalStudents(),
            'enrolled_students'       => $this->dashboardModel->getCurrentEnrolledStudents(),
            'total_sections'          => $this->dashboardModel->getTotalSections(),
            'active_school_years'     => $this->dashboardModel->getTotalActiveSchoolYears(),
            'required_documents'      => $this->dashboardModel->getTotalRequiredDocuments(),
            'pending_documents'       => $this->dashboardModel->getPendingDocumentsCount(),
            'verified_documents'      => $this->dashboardModel->getVerifiedDocumentsCount(),
            'rejected_documents'      => $this->dashboardModel->getRejectedDocumentsCount(),
            'recent_registrations'    => $this->dashboardModel->getRecentStudentRegistrations(),
            'recent_uploads'          => $this->dashboardModel->getRecentDocumentUploads(),
            'grade_level_summary'     => $this->dashboardModel->getGradeLevelSummary(),
            'document_status_summary' => $this->dashboardModel->getDocumentStatusSummary(),
            'registration_trend'      => $this->dashboardModel->getMonthlyRegistrationTrend(),
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
        'registration_trend',
    ], null);
    $data['error'] = 'Dashboard data could not be loaded. Please try again.';
}
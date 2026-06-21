<?php
session_start();

require_once __DIR__ . '/../../models/registrar/GraduatesModel.php';
require_once __DIR__ . '/../../../database/config/config.php';

    class GraduatesController{
        private $model;

        public function __construct($con){
            $this->model = new GraduatesModel($con);
        }

        /**
         * Summary cards + chart-ready analytics for the graduate dashboard.
         */
        public function dashboard(): array{
            return [
                'total_graduates'       => $this->model->getTotalGraduates(),
                'male_graduates'        => $this->model->getGenderCount('Male'),
                'female_graduates'      => $this->model->getGenderCount('Female'),
                'current_sy_graduates'  => $this->model->getCurrentSchoolYearGraduates(),
                'previous_sy_graduates' => $this->model->getPreviousSchoolYearGraduates(),
                'per_school_year'       => $this->model->getGraduatesPerSchoolYear(),
                'per_grade_level'       => $this->model->getGraduatesPerGradeLevel(),
                'per_section'           => $this->model->getGraduatesPerSection(),
            ];
        }

        /**
         * Paginated, filtered master list of graduates.
         */
        public function masterList($filters = [], $page = 1): array{
            $limit = 10;
            $page  = max(1, (int)$page);

            $totalRecords = $this->model->getTotalCount($filters);
            $totalPages   = $totalRecords > 0 ? (int)ceil($totalRecords / $limit) : 1;
            $page         = min($page, $totalPages);
            $offset       = ($page - 1) * $limit;

            return [
                'records'       => $this->model->getPaginated($limit, $offset, $filters) ?: [],
                'current_page'  => $page,
                'total_pages'   => $totalPages,
                'total_records' => $totalRecords,
                'limit'         => $limit,
            ];
        }

        /**
         * Full profile for a single graduate: personal info, academic history
         * timeline, section history (derived from academic history) and documents.
         */
        public function profile($graduateId): ?array{
            $graduate = $this->model->getGraduateProfile($graduateId);
            if(!$graduate){
                return null;
            }

            return [
                'graduate'         => $graduate,
                'academic_history' => $this->model->getAcademicHistoryByStudentId($graduate['student_id']),
                'documents'        => $this->model->getDocumentsByStudentId($graduate['student_id']),
            ];
        }

        public function filterOptions(): array{
            return [
                'school_years' => $this->model->getSchoolYearOptions(),
                'grade_levels' => $this->model->getGradeLevelOptions(),
                'sections'     => $this->model->getSectionOptions(),
            ];
        }

        /**
         * Stream the full filtered result set as a CSV file (opens in Excel).
         */
        public function exportExcel($filters = []): void{
            $records = $this->model->getAllForExport($filters);

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=graduates-master-list-' . date('Y-m-d') . '.csv');

            $out = fopen('php://output', 'w');
            fputcsv($out, ['LRN', 'Student Name', 'Gender', 'Grade Level', 'Section', 'Adviser', 'School Year', 'Date Graduated', 'Registrar', 'Status']);

            foreach($records as $row){
                fputcsv($out, [
                    $row['lrn'],
                    $row['student_full_name'],
                    $row['gender'],
                    $row['grade_level'],
                    $row['section_name'],
                    $row['adviser_name'],
                    $row['school_year'],
                    $row['graduation_date'],
                    $row['registrar_name'],
                    $row['enrollment_status'],
                ]);
            }

            fclose($out);
            exit();
        }

        /**
         * PDF export stub. No PDF library (e.g. Dompdf/TCPDF) is installed in this
         * project yet, so this records intent and bounces back with a flash notice.
         * Wire up a real library here when one is added.
         */
        public function exportPdf($filters = []): void{
            require_once __DIR__ . '/../../helpers/flashMessage.php';
            FlashMessage::setFlash('info', 'PDF export is not yet available. Install a PDF library (e.g. Dompdf) to enable this feature.');
            header('Location: ../../../resources/views/registrar/graduates-master-list.php');
            exit();
        }
    }

    //============ bootstrap ============//
    try{
        $controller = new GraduatesController($con);

        if(isset($_GET['export'])){
            $filters = [
                'search'         => trim($_GET['search'] ?? ''),
                'school_year_id' => $_GET['school_year_id'] ?? '',
                'grade_level'    => $_GET['grade_level'] ?? '',
                'section_id'     => $_GET['section_id'] ?? '',
                'gender'         => $_GET['gender'] ?? '',
                'status'         => $_GET['status'] ?? '',
            ];

            if($_GET['export'] === 'excel'){
                $controller->exportExcel($filters);
            }elseif($_GET['export'] === 'pdf'){
                $controller->exportPdf($filters);
            }
        }
    }catch(Exception $e){
        error_log($e->getMessage());
        $controller = null;
    }
?>

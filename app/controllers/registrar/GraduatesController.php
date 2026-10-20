<?php
session_start();

require_once __DIR__ . '/../../models/registrar/GraduatesModel.php';
require_once __DIR__ . '/../../models/SchoolSettingsModel.php';
require_once __DIR__ . '/../../core/errorHandler.php';
require_once __DIR__ . '/../../../database/config/config.php';
require_once __DIR__ . '/../../helpers/graduatesPdfTemplate.php';

    class GraduatesController{
        private $model;
        private $settingsModel;

        public function __construct($con){
            $this->model = new GraduatesModel($con);
            $this->settingsModel = new SchoolSettingsModel($con);
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
                'current_sy_id'         => $this->model->getActiveSchoolYearId(),
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
         * Every matching record, unpaginated -- used for Print (so a batch with
         * more than one page of results doesn't get silently cut off) and for
         * exports, which already needed the full set.
         * @param array $filters
         * @return array
         */
        public function allForPrint($filters = []): array{
            return $this->model->getAllForExport($filters);
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

        /**
         * Full filtered result set, grouped by section, for the printable
         * per-section report.
         * @param array $filters
         * @return array<string, array{section_name:string, grade_level:string, adviser_name:?string, students:array}>
         */
        public function bySection($filters = []): array{
            $records = $this->model->getAllForExportBySection($filters);

            $groups = [];
            foreach($records as $row){
                $key = $row['section_name'] ?? 'Unassigned';
                if(!isset($groups[$key])){
                    $groups[$key] = [
                        'section_name' => $row['section_name'] ?? 'Unassigned',
                        'grade_level'  => $row['grade_level'],
                        'adviser_name' => $row['adviser_name'],
                        'students'     => [],
                    ];
                }
                $groups[$key]['students'][] = $row;
            }

            return $groups;
        }

        public function filterOptions(): array{
            return [
                'school_years' => $this->model->getSchoolYearOptions(),
                'sections'     => $this->model->getSectionOptions(),
            ];
        }

        /**
         * "Batch X" label inferred from the records actually being shown/printed,
         * not from whether a School Year filter was explicitly picked -- if every
         * record in the result set happens to belong to the same school year
         * (the common case), that year is the batch. Mixed-year results (or an
         * empty set) show no batch label.
         * @param array $records rows with a 'school_year' key
         * @return string
         */
        public static function inferBatchLabel(array $records): string{
            $years = array_unique(array_filter(array_column($records, 'school_year')));
            return count($years) === 1 ? (string) reset($years) : '';
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
         * Stream the full filtered result set as a landscape PDF via Dompdf.
         */
        public function exportPdf($filters = []): void{
            require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

            $records = $this->model->getAllForExport($filters);

            $meta = $this->settingsModel->getAll();
            $meta['batch_label'] = self::inferBatchLabel($records);

            $html = GraduatesPdfTemplate::render($records, $meta);

            $options = new \Dompdf\Options();
            $options->set('isRemoteEnabled', false);
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->setPaper('legal', 'landscape');
            $dompdf->loadHtml($html);
            $dompdf->render();
            $dompdf->stream('graduates-master-list-' . date('Y-m-d') . '.pdf', ['Attachment' => true]);
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
        ErrorHandler::log($e, 'registrar/GraduatesController (bootstrap)');
        $controller = null;
    }
?>

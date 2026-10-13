<?php
session_start();

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';
require_once __DIR__ . '/../../core/errorHandler.php';
require_once __DIR__ . '/../../../database/config/config.php';
require_once __DIR__ . '/../../helpers/flashMessage.php';
require_once __DIR__ . '/../../helpers/auditLogs.php';
require_once __DIR__ . '/../../helpers/csrf.php';
require_once __DIR__ . '/../../helpers/fileUpload.php';
require_once __DIR__ . '/../../middleware/Auth.php';
require_once __DIR__ . '/../../models/registrar/SchoolYearModel.php';
require_once __DIR__ . '/../../services/SchoolYearImportService.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

    class SchoolYearImportController{
        private $con;
        private $model;
        private $auditLogs;
        private $importService;
        private $maxUploadBytes = 2097152; // 2 MB
        private $maxDataRows = 1000;

        public function __construct($con){
            $this->con = $con;
            $this->model = new SchoolYearModel($con);
            $this->auditLogs = new AuditLogs($con);
            $this->importService = new SchoolYearImportService();
        }

        /**
         * Handle the uploaded file: parse, validate, insert-only into `school_year`,
         * write one audit log entry for the batch, and store the row-level
         * breakdown in the session for the results view to render.
         */
        public function import(): void{
            $redirectTo = BASE_URL . '/resources/views/registrar/import-school-years.php';

            try{
                set_time_limit(120);

                $file = $_FILES['import_file'] ?? null;
                if(!$file || $file['error'] !== UPLOAD_ERR_OK){
                    FlashMessage::setFlash('error', 'Please choose a file to import.');
                    header('Location: ' . $redirectTo);
                    exit();
                }

                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if(!in_array($ext, ['xlsx', 'xls', 'csv'])){
                    FlashMessage::setFlash('error', 'Invalid file type. Please upload a .xlsx, .xls, or .csv file.');
                    header('Location: ' . $redirectTo);
                    exit();
                }

                if($file['size'] > $this->maxUploadBytes){
                    FlashMessage::setFlash('error', 'File is too large. Maximum allowed size is 2 MB.');
                    header('Location: ' . $redirectTo);
                    exit();
                }

                $relativePath = FileUpload::upload($file, 'imports', 'sy_import_' . ($_SESSION['id'] ?? 'unknown'), $this->maxUploadBytes);
                $absolutePath = dirname(__DIR__, 3) . '/' . $relativePath;

                $parsed = $this->importService->parseSpreadsheet($absolutePath);
                if($parsed['error']){
                    FlashMessage::setFlash('error', $parsed['error']);
                    header('Location: ' . $redirectTo);
                    exit();
                }

                if(count($parsed['rows']) > $this->maxDataRows){
                    FlashMessage::setFlash('error', "Too many rows in file. Maximum allowed is {$this->maxDataRows}.");
                    header('Location: ' . $redirectTo);
                    exit();
                }

                $existingKeys = [];
                foreach($this->model->index() as $sy){
                    $existingKeys[SchoolYearImportService::normalizeSchoolYearKey($sy['school_year'])] = true;
                }
                $hasExistingActive = !empty($this->model->findActive());
                $activeAssignedInFile = false;

                $seen = [];
                $results = [
                    'total' => 0, 'inserted' => 0, 'skipped' => 0, 'failed' => 0,
                    'details' => [],
                ];

                foreach($parsed['rows'] as $row){
                    $results['total']++;
                    $verdict = $this->importService->validateRow($row['cells'], $row['rowNumber'], $seen, $existingKeys, $hasExistingActive, $activeAssignedInFile);

                    if($verdict['status'] === 'ok'){
                        if($this->model->create($verdict['data'])){
                            $results['inserted']++;
                            $key = SchoolYearImportService::normalizeSchoolYearKey($verdict['data']['school_year']);
                            $existingKeys[$key] = true;
                            $results['details'][] = $verdict;
                        }else{
                            $verdict['status'] = 'fail';
                            $verdict['reason'] = 'Database insert failed.';
                            $results['failed']++;
                            $results['details'][] = $verdict;
                        }
                    }elseif($verdict['status'] === 'skip'){
                        $results['skipped']++;
                        $results['details'][] = $verdict;
                    }else{
                        $results['failed']++;
                        $results['details'][] = $verdict;
                    }
                }

                $this->auditLogs->log(
                    $_SESSION['id'] ?? null,
                    $_SESSION['role'] ?? 'unknown',
                    'IMPORT SCHOOL YEARS',
                    'SCHOOL_YEAR',
                    null,
                    'school_year',
                    "{$results['inserted']} school years inserted, {$results['skipped']} skipped, {$results['failed']} failed",
                );

                $_SESSION['sy_import_results'] = $results;

                $flashType = ($results['failed'] > 0 || $results['skipped'] > 0) ? 'warning' : 'success';
                FlashMessage::setFlash($flashType, "Import complete: {$results['inserted']} inserted, {$results['skipped']} skipped, {$results['failed']} failed.");
                header('Location: ' . $redirectTo);
                exit();
            }catch(Exception $e){
                ErrorHandler::redirect($e, $redirectTo, 'Failed to process the import file. Please check the file and try again.', 'SchoolYearImportController::import');
            }
        }

        /**
         * Stream an .xlsx template with the header row plus a couple of sample
         * data rows, so a registrar can see the expected format right away.
         */
        public function downloadTemplate(): void{
            try{
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->fromArray(SchoolYearImportService::TEMPLATE_HEADERS, null, 'A1');
                $columnCount = count(SchoolYearImportService::TEMPLATE_HEADERS);
                $lastCol = Coordinate::stringFromColumnIndex($columnCount);
                $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);
                for($i = 1; $i <= $columnCount; $i++){
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
                }

                $sampleRows = [
                    ['2030-2031', '2030-06-01', '2031-03-31', 'inactive'],
                    ['2031-2032', '2031-06-01', '2032-03-31', ''],
                    ['2032-2033', '2032-06-01', '2033-03-31', 'archived'],
                ];
                $sheet->fromArray($sampleRows, null, 'A2');

                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="school_years_import_template.xlsx"');
                header('Cache-Control: max-age=0');
                $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                $writer->save('php://output');
                exit();
            }catch(Exception $e){
                ErrorHandler::redirect($e, BASE_URL . '/resources/views/registrar/import-school-years.php', 'Failed to generate the template file.', 'SchoolYearImportController::downloadTemplate');
            }
        }

        /**
         * Stream an .xlsx export of every school year currently on record.
         */
        public function exportSchoolYears(): void{
            try{
                $schoolYears = $this->model->index();

                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $headers = ['id', 'school_year', 'start_date', 'end_date', 'status', 'ended_at', 'auto_ended'];
                $sheet->fromArray($headers, null, 'A1');
                $lastCol = Coordinate::stringFromColumnIndex(count($headers));
                $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);
                for($i = 1; $i <= count($headers); $i++){
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
                }

                $data = array_map(function($sy){
                    return [
                        $sy['id'],
                        $sy['school_year'],
                        $sy['start_date'],
                        $sy['end_date'],
                        $sy['status'],
                        $sy['ended_at'] ?? '',
                        ($sy['auto_ended'] ?? 0) == 1 ? 'yes' : 'no',
                    ];
                }, $schoolYears ?: []);
                $sheet->fromArray($data, null, 'A2');

                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="school_years_export_' . date('Ymd_His') . '.xlsx"');
                header('Cache-Control: max-age=0');
                $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                $writer->save('php://output');
                exit();
            }catch(Exception $e){
                ErrorHandler::redirect($e, BASE_URL . '/resources/views/registrar/school-year.php', 'Failed to generate the export file.', 'SchoolYearImportController::exportSchoolYears');
            }
        }
    }

//=================================== bootstrap ====================================//
try{
    AuthRole::allowOnly(['registrar']);

    $controller = new SchoolYearImportController($con);

    if($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['download'] ?? '') === 'template'){
        $controller->downloadTemplate();
    }

    if($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['export'] ?? '') === 'excel'){
        $controller->exportSchoolYears();
    }

    if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_school_years'])){
        Csrf::requireValidOnPost(BASE_URL . '/resources/views/registrar/import-school-years.php');
        $controller->import();
    }
}catch(Exception $e){
    ErrorHandler::log($e, 'SchoolYearImportController (bootstrap)');
    exit();
}

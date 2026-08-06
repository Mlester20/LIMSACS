<?php
session_start();

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';
require_once __DIR__ . '/../../core/errorHandler.php';
require_once __DIR__ . '/../../../database/config/config.php';
require_once __DIR__ . '/../../helpers/flashMessage.php';
require_once __DIR__ . '/../../helpers/auditLogs.php';
require_once __DIR__ . '/../../helpers/csrf.php';
require_once __DIR__ . '/../../helpers/fileUpload.php';
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../models/registrar/StudentsModel.php';
require_once __DIR__ . '/../../services/StudentImportService.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

    class StudentImportController{
        private $con;
        private $model;
        private $auditLogs;
        private $importService;
        private $maxUploadBytes = 5242880; // 5 MB
        private $maxDataRows = 5000;

        public function __construct($con){
            $this->con = $con;
            $this->model = new StudentsModel($con);
            $this->auditLogs = new AuditLogs($con);
            $this->importService = new StudentImportService();
        }

        /**
         * Handle the uploaded file: parse, validate, insert-only into `students`,
         * write one audit log entry for the batch, and store the row-level
         * breakdown in the session for the results view to render.
         */
        public function import(): void{
            $redirectTo = BASE_URL . '/resources/views/registrar/import-students.php';

            try{
                set_time_limit(180);

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
                    FlashMessage::setFlash('error', 'File is too large. Maximum allowed size is 5 MB.');
                    header('Location: ' . $redirectTo);
                    exit();
                }

                $relativePath = FileUpload::upload($file, 'imports', 'import_' . ($_SESSION['id'] ?? 'unknown'), $this->maxUploadBytes);
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

                $existingLrns = $this->model->getExistingLrns();
                $seen = [];
                $results = ['total' => 0, 'inserted' => 0, 'skipped' => 0, 'failed' => 0, 'details' => []];

                foreach($parsed['rows'] as $row){
                    $results['total']++;
                    $verdict = $this->importService->validateRow($row['cells'], $row['rowNumber'], $seen, $existingLrns);

                    if($verdict['status'] === 'ok'){
                        if($this->model->create($verdict['data'])){
                            $results['inserted']++;
                            if(!empty($verdict['data']['lrn'])){
                                $existingLrns[$verdict['data']['lrn']] = true;
                            }
                        }else{
                            $verdict['status'] = 'fail';
                            $verdict['reason'] = 'Database insert failed (possible duplicate or constraint error).';
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
                    'IMPORT STUDENTS',
                    'STUDENTS',
                    null,
                    'students',
                    "{$results['inserted']} inserted, {$results['skipped']} skipped, {$results['failed']} failed",
                );

                $_SESSION['import_results'] = $results;

                $flashType = ($results['failed'] > 0 || $results['skipped'] > 0) ? 'warning' : 'success';
                FlashMessage::setFlash($flashType, "Import complete: {$results['inserted']} inserted, {$results['skipped']} skipped, {$results['failed']} failed.");
                header('Location: ' . $redirectTo);
                exit();
            }catch(Exception $e){
                ErrorHandler::redirect($e, $redirectTo, 'Failed to process the import file. Please check the file and try again.', 'StudentImportController::import');
            }
        }

        /**
         * Stream a blank .xlsx template with the exact students-table header row.
         */
        public function downloadTemplate(): void{
            try{
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->fromArray(StudentImportService::TEMPLATE_HEADERS, null, 'A1');
                $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count(StudentImportService::TEMPLATE_HEADERS));
                $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);
                foreach(range('A', $lastCol) as $col){
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="students_import_template.xlsx"');
                header('Cache-Control: max-age=0');
                $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                $writer->save('php://output');
                exit();
            }catch(Exception $e){
                ErrorHandler::redirect($e, BASE_URL . '/resources/views/registrar/import-students.php', 'Failed to generate the template file.', 'StudentImportController::downloadTemplate');
            }
        }
    }

//=================================== bootstrap ====================================//
try{
    AuthRole::allowOnly(['registrar']);

    $controller = new StudentImportController($con);

    if($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['download'] ?? '') === 'template'){
        $controller->downloadTemplate();
    }

    if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_students'])){
        Csrf::requireValidOnPost(BASE_URL . '/resources/views/registrar/import-students.php');
        $controller->import();
    }
}catch(Exception $e){
    ErrorHandler::log($e, 'StudentImportController (bootstrap)');
    exit();
}

<?php
session_start();

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';
require_once __DIR__ . '/../../../database/config/config.php';
require_once __DIR__ . '/../../core/errorHandler.php';
require_once __DIR__ . '/../../helpers/auditLogs.php';
require_once __DIR__ . '/../../middleware/Auth.php';
require_once __DIR__ . '/../../models/registrar/StudentsModel.php';
require_once __DIR__ . '/../../models/registrar/ParentGuardiansModel.php';
require_once __DIR__ . '/../../models/registrar/AcademicHistoryModel.php';
require_once __DIR__ . '/../../models/registrar/SectionsModel.php';
require_once __DIR__ . '/../../models/registrar/SchoolYearModel.php';
require_once __DIR__ . '/../../models/registrar/GraduatesModel.php';
require_once __DIR__ . '/../../models/registrar/StudentsDocumentModel.php';
require_once __DIR__ . '/../../models/admin/UsersModel.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

    /**
     * Supplementary, human-readable backup — NOT a substitute for the real
     * restorable backup (the SQL dump in backups/). This streams a multi-sheet
     * .xlsx snapshot of the core tables so the client has a readable copy of
     * their data without needing a database tool to open backups/*.sql.
     */
    class BackupController{
        private $con;
        private $students;
        private $parentGuardians;
        private $academicHistory;
        private $sections;
        private $schoolYear;
        private $graduates;
        private $studentsDocuments;
        private $users;
        private $auditLogs;

        public function __construct($con){
            $this->con = $con;
            $this->students = new StudentsModel($con);
            $this->parentGuardians = new ParentGuardiansModel($con);
            $this->academicHistory = new AcademicHistoryModel($con);
            $this->sections = new SectionsModel($con);
            $this->schoolYear = new SchoolYearModel($con);
            $this->graduates = new GraduatesModel($con);
            $this->studentsDocuments = new StudentsDocumentModel($con);
            $this->users = new UsersModel($con);
            $this->auditLogs = new AuditLogs($con);
        }

        /**
         * Columns that are fully numeric strings (LRN, phone numbers) — left
         * as Excel's default "General" format, Excel re-interprets them as
         * numbers: long IDs render as scientific notation (1.36E+11) and any
         * leading zero (e.g. a 09xx mobile number) is silently stripped.
         */
        private const TEXT_COLUMNS = ['lrn', 'contact_number', 'father_contact', 'mother_contact', 'guardian_contact'];

        /**
         * Write one sheet: bold header row, autosized columns, rows taken
         * verbatim from the given associative-array records (keys = headers).
         * A new Spreadsheet() already has one empty default sheet, so the
         * first call reuses it instead of creating a redundant extra sheet.
         */
        private function writeSheet(Spreadsheet $spreadsheet, string $title, array $headers, array $records, bool $isFirstSheet = false): void{
            $sheet = $isFirstSheet ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
            $sheet->setTitle($title);

            $sheet->fromArray($headers, null, 'A1');
            $lastCol = Coordinate::stringFromColumnIndex(count($headers));
            $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);

            $rows = array_map(function($record) use ($headers){
                return array_map(function($key) use ($record){
                    return $record[$key] ?? '';
                }, $headers);
            }, $records);

            if(!empty($rows)){
                $sheet->fromArray($rows, null, 'A2');
            }

            // Force numeric-looking identifier/phone columns to Text, both the
            // column format (for any rows added later) and each existing cell's
            // stored type (fromArray() infers type from the value otherwise).
            foreach(self::TEXT_COLUMNS as $textColumn){
                $colIndex = array_search($textColumn, $headers, true);
                if($colIndex === false) continue;
                $colLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
                $sheet->getStyle("{$colLetter}:{$colLetter}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
                foreach($rows as $rowOffset => $row){
                    $value = $row[$colIndex] ?? '';
                    if($value === '') continue;
                    $sheet->setCellValueExplicit("{$colLetter}" . (2 + $rowOffset), (string)$value, DataType::TYPE_STRING);
                }
            }

            for($i = 1; $i <= count($headers); $i++){
                $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
            }
        }

        public function exportExcel(): void{
            try{
                $spreadsheet = new Spreadsheet();

                $this->writeSheet(
                    $spreadsheet, 'Students',
                    ['id', 'lrn', 'first_name', 'middle_name', 'last_name', 'suffix', 'gender', 'birth_date', 'age', 'place_of_birth', 'nationality', 'religion', 'address', 'contact_number'],
                    $this->students->index(),
                    true
                );

                $this->writeSheet(
                    $spreadsheet, 'Parents-Guardians',
                    ['id', 'student_id', 'student_first_name', 'student_last_name', 'father_name', 'father_occupation', 'father_contact', 'mother_name', 'mother_occupation', 'mother_contact', 'guardian_name', 'guardian_relationship', 'guardian_contact'],
                    $this->parentGuardians->index()
                );

                $this->writeSheet(
                    $spreadsheet, 'Academic History',
                    ['id', 'lrn', 'student_full_name', 'school_year', 'grade_level', 'section_name', 'enrollment_status', 'created_at'],
                    $this->academicHistory->getAllForExport()
                );

                $this->writeSheet(
                    $spreadsheet, 'Sections',
                    ['id', 'section_name', 'grade_level', 'adviser_name', 'school_year', 'max_students'],
                    $this->sections->index()
                );

                $this->writeSheet(
                    $spreadsheet, 'School Years',
                    ['id', 'school_year', 'start_date', 'end_date', 'status'],
                    $this->schoolYear->index()
                );

                $this->writeSheet(
                    $spreadsheet, 'Graduates',
                    ['lrn', 'student_full_name', 'gender', 'grade_level', 'enrollment_status', 'section_name', 'school_year', 'graduation_date', 'registrar_name', 'adviser_name'],
                    $this->graduates->getAllForExport([])
                );

                $this->writeSheet(
                    $spreadsheet, 'Student Documents',
                    ['id', 'student_first_name', 'student_last_name', 'document_type_name', 'status', 'remarks', 'uploaded_by_name', 'uploaded_at'],
                    $this->studentsDocuments->index()
                );

                // Deliberately excludes the password hash — UsersModel::index() never selects it.
                $this->writeSheet(
                    $spreadsheet, 'Users',
                    ['id', 'full_name', 'email', 'role', 'created_at'],
                    $this->users->index()
                );

                $spreadsheet->setActiveSheetIndex(0);

                $this->auditLogs->log(
                    $_SESSION['id'] ?? null,
                    $_SESSION['role'] ?? 'unknown',
                    'EXPORT BACKUP',
                    'BACKUP',
                    null,
                    null,
                    ($_SESSION['full_name'] ?? 'Registrar') . ' exported an Excel database snapshot',
                );

                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="limsacs_backup_' . date('Y-m-d_His') . '.xlsx"');
                header('Cache-Control: max-age=0');
                $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                $writer->save('php://output');
                exit();
            }catch(Exception $e){
                ErrorHandler::redirect($e, BASE_URL . '/resources/views/registrar/backup.php', 'Failed to generate the backup file.', 'BackupController::exportExcel');
            }
        }
    }

//=================================== bootstrap ====================================//
try{
    AuthRole::allowOnly(['registrar']);

    $controller = new BackupController($con);

    if($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['export'] ?? '') === 'excel'){
        $controller->exportExcel();
    }
}catch(Exception $e){
    ErrorHandler::log($e, 'BackupController (bootstrap)');
    exit();
}

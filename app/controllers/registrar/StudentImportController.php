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
require_once __DIR__ . '/../../models/registrar/ParentGuardiansModel.php';
require_once __DIR__ . '/../../models/registrar/AcademicHistoryModel.php';
require_once __DIR__ . '/../../models/registrar/GraduatesModel.php';
require_once __DIR__ . '/../../models/registrar/SchoolYearModel.php';
require_once __DIR__ . '/../../models/registrar/SectionsModel.php';
require_once __DIR__ . '/../../services/EnrollmentService.php';
require_once __DIR__ . '/../../services/StudentImportService.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

    class StudentImportController{
        private $con;
        private $model;
        private $parentGuardians;
        private $academicHistory;
        private $graduates;
        private $schoolYear;
        private $sections;
        private $auditLogs;
        private $importService;
        private $maxUploadBytes = 5242880; // 5 MB
        private $maxDataRows = 5000;

        public function __construct($con){
            $this->con = $con;
            $this->model = new StudentsModel($con);
            $this->parentGuardians = new ParentGuardiansModel($con);
            $this->academicHistory = new AcademicHistoryModel($con);
            $this->graduates = new GraduatesModel($con);
            $this->schoolYear = new SchoolYearModel($con);
            $this->sections = new SectionsModel($con);
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

                // Built once per batch, not per row: school-year string -> id, and
                // "section name|school_year_id" -> id, plus section rows by id for
                // the non-blocking grade_level cross-check note in validateGroupC().
                $schoolYearLookup = [];
                $sectionsById = [];
                $sectionLookup = [];
                foreach($this->schoolYear->index() as $sy){
                    $key = strtolower(str_replace(['–', '—'], '-', trim($sy['school_year'])));
                    $key = preg_replace('/\s*-\s*/', '-', $key);
                    $schoolYearLookup[$key] = (int)$sy['id'];
                }
                foreach($this->sections->index() as $sec){
                    $sectionsById[(int)$sec['id']] = $sec;
                    $sectionLookup[strtolower(trim($sec['section_name'])) . '|' . $sec['school_year_id']] = (int)$sec['id'];
                }

                $seen = [];
                $results = [
                    'total' => 0, 'inserted' => 0, 'skipped' => 0, 'failed' => 0,
                    'parents_added' => 0, 'academic_added' => 0, 'academic_skipped' => 0,
                    'graduates_added' => 0, 'graduates_skipped' => 0,
                    'details' => [],
                ];

                foreach($parsed['rows'] as $row){
                    $results['total']++;
                    $verdict = $this->importService->validateRow($row['cells'], $row['rowNumber'], $seen, $existingLrns);
                    $verdict['parent'] = null;
                    $verdict['academic'] = null;
                    $verdict['graduate'] = null;

                    if($verdict['status'] === 'ok'){
                        if($this->model->create($verdict['data'])){
                            $results['inserted']++;
                            if(!empty($verdict['data']['lrn'])){
                                $existingLrns[$verdict['data']['lrn']] = true;
                            }
                            $studentId = $this->con->insert_id;

                            // Group B — parent/guardian
                            $groupB = $this->importService->validateGroupB($row['cells']);
                            if($groupB !== null){
                                $groupB['student_id'] = $studentId;
                                if($this->parentGuardians->create($groupB) === true){
                                    $results['parents_added']++;
                                    $verdict['parent'] = ['status' => 'added', 'reason' => null];
                                }else{
                                    $verdict['parent'] = ['status' => 'failed', 'reason' => 'database error'];
                                }
                            }else{
                                $verdict['parent'] = ['status' => 'skipped', 'reason' => 'no data'];
                            }

                            // Group C — academic history / enrollment
                            $groupC = $this->importService->validateGroupC($row['cells'], $schoolYearLookup, $sectionLookup, $sectionsById);
                            $academicHistoryId = null;
                            $enrollmentStatus = null;
                            $gradeLevel = null;
                            if($groupC['status'] === 'ok'){
                                $academicData = $groupC['data'] + ['student_id' => $studentId, 'enrolled_by' => $_SESSION['id'] ?? null];
                                if($this->academicHistory->create($academicData)){
                                    $academicHistoryId = $this->con->insert_id;
                                    $results['academic_added']++;
                                    $verdict['academic'] = ['status' => 'added', 'reason' => $groupC['note']];
                                    $enrollmentStatus = $groupC['data']['enrollment_status'];
                                    $gradeLevel = $groupC['data']['grade_level'];
                                }else{
                                    $results['academic_skipped']++;
                                    $verdict['academic'] = ['status' => 'failed', 'reason' => 'database error'];
                                }
                            }elseif($groupC['status'] === 'skip'){
                                $results['academic_skipped']++;
                                $verdict['academic'] = ['status' => 'skipped', 'reason' => $groupC['reason']];
                            }else{
                                $verdict['academic'] = ['status' => 'skipped', 'reason' => 'no data'];
                            }

                            // Group D — graduation info (only reachable if Group C succeeded)
                            if($academicHistoryId !== null){
                                $groupD = $this->importService->validateGroupD($row['cells'], $enrollmentStatus, $gradeLevel);
                                if($groupD['status'] === 'ok'){
                                    $graduateData = $groupD['data'] + [
                                        'student_id' => $studentId,
                                        'academic_history_id' => $academicHistoryId,
                                        'recorded_by' => $_SESSION['id'] ?? null,
                                    ];
                                    if($this->graduates->create($graduateData)){
                                        $results['graduates_added']++;
                                        $verdict['graduate'] = ['status' => 'added', 'reason' => null];
                                    }else{
                                        $results['graduates_skipped']++;
                                        $verdict['graduate'] = ['status' => 'failed', 'reason' => 'database error'];
                                    }
                                }elseif($groupD['status'] === 'skip'){
                                    $results['graduates_skipped']++;
                                    $verdict['graduate'] = ['status' => 'skipped', 'reason' => $groupD['reason']];
                                }else{
                                    $verdict['graduate'] = ['status' => 'skipped', 'reason' => 'no data'];
                                }
                            }else{
                                $verdict['graduate'] = ['status' => 'skipped', 'reason' => 'no data'];
                            }

                            $results['details'][] = $verdict;
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
                    "{$results['inserted']} students inserted, {$results['skipped']} skipped, {$results['failed']} failed; "
                    . "{$results['parents_added']} parent/guardian records added; "
                    . "{$results['academic_added']} academic history records added ({$results['academic_skipped']} skipped); "
                    . "{$results['graduates_added']} graduate records added ({$results['graduates_skipped']} skipped)",
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
         * Stream an .xlsx template with the header row plus sample data rows,
         * so a registrar can see the expected format and try an import right away.
         */
        public function downloadTemplate(): void{
            try{
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->fromArray(StudentImportService::TEMPLATE_HEADERS, null, 'A1');
                $columnCount = count(StudentImportService::TEMPLATE_HEADERS);
                $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnCount);
                $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);
                for($i = 1; $i <= $columnCount; $i++){
                    $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
                }

                $sheet->fromArray($this->sampleRows(), null, 'A2');

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

        /**
         * 10 sample rows illustrating every optional group (parent/guardian,
         * academic history, graduation), so the template is importable as-is
         * for a quick test. Values are pulled live from this DB rather than
         * hardcoded, so the sample rows stay importable even right after a
         * fresh/empty database — no "school year not found" mismatch.
         *
         * The two graduation examples deliberately use TWO DIFFERENT school
         * years (the oldest one on record and the current/latest one) to
         * demonstrate that a single import file can mix graduates from any
         * school year, not just the active one. If there's no school year
         * at all yet, Group C/D cells are simply left blank (rows still
         * import fine as Group A/B only).
         * @return array<int, array<string, string>> each row keyed by TEMPLATE_HEADERS
         */
        private function sampleRows(): array{
            $blank = array_fill_keys(StudentImportService::TEMPLATE_HEADERS, '');

            // All school years on record (any status), oldest first, so we can
            // deliberately pick two different years for the graduation examples.
            $allYears = $this->schoolYear->index();
            usort($allYears, function($a, $b){ return strcmp($a['school_year'], $b['school_year']); });
            $oldestYear = !empty($allYears) ? $allYears[0] : null;
            $latestYear = !empty($allYears) ? $allYears[count($allYears) - 1] : null;

            $activeYears = $this->schoolYear->findActive();
            $currentYear = !empty($activeYears) ? $activeYears[0] : $latestYear;

            $allSections = $this->sections->index();
            $pickSection = function(?array $yearRow, string $gradeLevel) use ($allSections): string {
                if ($yearRow === null) return '';
                foreach ($allSections as $sec) {
                    if ((int)$sec['school_year_id'] === (int)$yearRow['id'] && $sec['grade_level'] === $gradeLevel) {
                        return $sec['section_name'];
                    }
                }
                return '';
            };

            $gradeLevel1 = 'Grade 1';
            $gradeLevel6 = EnrollmentService::TERMINAL_GRADE_LEVEL;

            $currentSchoolYear = $currentYear['school_year'] ?? '';
            $section1 = $pickSection($currentYear, $gradeLevel1);

            // Group C cells are only meaningful once there's a school year to attach to.
            $enroll = function(string $gradeLevel, string $section, string $status = 'Enrolled') use ($currentSchoolYear): array {
                if ($currentSchoolYear === '') return [];
                return ['school_year' => $currentSchoolYear, 'grade_level' => $gradeLevel, 'section' => $section, 'enrollment_status' => $status];
            };
            // Group D: one graduate in the OLDEST year on record, one in the CURRENT/latest year.
            $graduateIn = function(?array $yearRow, string $honors) use ($pickSection, $gradeLevel6): array {
                if ($yearRow === null) return [];
                return [
                    'school_year' => $yearRow['school_year'], 'grade_level' => $gradeLevel6, 'section' => $pickSection($yearRow, $gradeLevel6),
                    'enrollment_status' => 'Graduated', 'graduation_date' => $yearRow['end_date'], 'honors' => $honors,
                ];
            };

            $rows = [
                ['lrn' => '136000000001', 'first_name' => 'Juan', 'last_name' => 'Dela Cruz', 'gender' => 'Male', 'birth_date' => '2015-06-12', 'place_of_birth' => 'Manila', 'nationality' => 'Filipino', 'religion' => 'Catholic', 'address' => '123 Rizal St, Manila', 'contact_number' => '09171234561'],
                ['lrn' => '136000000002', 'first_name' => 'Maria', 'last_name' => 'Santos', 'gender' => 'Female', 'birth_date' => '2015-08-20', 'place_of_birth' => 'Quezon City', 'nationality' => 'Filipino', 'religion' => 'Catholic', 'address' => '45 Bonifacio Ave, QC', 'contact_number' => '09171234562'],
                array_merge(['lrn' => '136000000003', 'first_name' => 'Jose', 'last_name' => 'Garcia', 'gender' => 'Male', 'birth_date' => '2015-03-05', 'place_of_birth' => 'Cebu City', 'nationality' => 'Filipino', 'religion' => 'Catholic', 'address' => '78 Mabini St, Cebu',
                    'father_name' => 'Pedro Garcia', 'father_occupation' => 'Driver', 'father_contact' => '09181234563',
                    'mother_name' => 'Ana Garcia', 'mother_occupation' => 'Vendor', 'mother_contact' => '09191234563']),
                array_merge(['lrn' => '136000000004', 'first_name' => 'Angela', 'last_name' => 'Reyes', 'gender' => 'Female', 'birth_date' => '2015-01-15', 'place_of_birth' => 'Manila', 'nationality' => 'Filipino', 'religion' => 'Catholic', 'address' => '12 Luna St, Manila'], $enroll($gradeLevel1, $section1)),
                array_merge(['lrn' => '136000000005', 'first_name' => 'Mark', 'last_name' => 'Villanueva', 'gender' => 'Male', 'birth_date' => '2015-09-02', 'place_of_birth' => 'Pasig', 'nationality' => 'Filipino', 'religion' => 'Catholic', 'address' => '9 Ortigas Ave, Pasig'], $enroll($gradeLevel1, '')), // section left blank on purpose
                array_merge(['lrn' => '136000000006', 'first_name' => 'Krystal', 'last_name' => 'Aquino', 'gender' => 'Female', 'birth_date' => '2015-11-30', 'place_of_birth' => 'Makati', 'nationality' => 'Filipino', 'religion' => 'Catholic', 'address' => '5 Ayala Ave, Makati',
                    'father_name' => 'Ramon Aquino', 'father_contact' => '09201234566', 'mother_name' => 'Liza Aquino', 'mother_contact' => '09211234566'], $enroll($gradeLevel1, $section1)),
                array_merge(['lrn' => '136000000007', 'first_name' => 'Paolo', 'last_name' => 'Torres', 'gender' => 'Male', 'birth_date' => '2014-07-18', 'place_of_birth' => 'Taguig', 'nationality' => 'Filipino', 'religion' => 'Catholic', 'address' => '3 McKinley Rd, Taguig'], $enroll($gradeLevel1, $section1, 'Transferred')),
                array_merge(['lrn' => '136000000008', 'first_name' => 'Bea', 'last_name' => 'Fernandez', 'gender' => 'Female', 'birth_date' => '2014-04-25', 'place_of_birth' => 'Manila', 'nationality' => 'Filipino', 'religion' => 'Catholic', 'address' => '21 Taft Ave, Manila'], $enroll($gradeLevel1, $section1, 'Dropped')),
                array_merge(['lrn' => '136000000009', 'first_name' => 'Miguel', 'last_name' => 'Ramos', 'gender' => 'Male', 'birth_date' => '1998-02-10', 'place_of_birth' => 'Manila', 'nationality' => 'Filipino', 'religion' => 'Catholic', 'address' => '14 Espana Blvd, Manila', 'remarks' => 'Graduated in the oldest school year on record'], $graduateIn($oldestYear, 'With Honors')),
                array_merge(['lrn' => '136000000010', 'first_name' => 'Samantha', 'last_name' => 'Lopez', 'gender' => 'Female', 'birth_date' => '2013-05-22', 'place_of_birth' => 'Manila', 'nationality' => 'Filipino', 'religion' => 'Catholic', 'address' => '30 Recto Ave, Manila',
                    'father_name' => 'Carlos Lopez', 'father_contact' => '09221234570', 'mother_name' => 'Grace Lopez', 'mother_contact' => '09231234570', 'remarks' => 'Graduated in the current/latest school year'], $graduateIn($currentYear, 'With High Honors')),
            ];

            return array_map(function($row) use ($blank){
                return array_values(array_merge($blank, $row));
            }, $rows);
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

<?php
session_start();

require_once __DIR__ . '/../../../database/config/config.php';
require_once __DIR__ . '/../../core/errorHandler.php';
require_once __DIR__ . '/../../helpers/flashMessage.php';
require_once __DIR__ . '/../../helpers/auditLogs.php';
require_once __DIR__ . '/../../helpers/csrf.php';
require_once __DIR__ . '/../../models/teacher/StudentsModel.php';
require_once __DIR__ . '/../../models/teacher/ParentGuardiansModel.php';
require_once __DIR__ . '/../../models/registrar/ParentGuardiansModel.php';

    class ParentGuardiansController{
        private $itemsPerPage = 10;
        private $teacherStudents;
        private $teacherGuardians;
        private $parentGuardians;
        protected $auditLogs;

        public function __construct($con){
            $this->teacherStudents = new TeacherStudentsModel($con);
            $this->teacherGuardians = new TeacherParentGuardiansModel($con);
            $this->parentGuardians = new ParentGuardiansModel($con);
            $this->auditLogs = new AuditLogs($con);
        }

        /**
         * Get paginated parent/guardian records for students assigned to this teacher
         * @param int $teacher_id
         * @param int $page
         * @return array
         */
        public function index($teacher_id, $page = 1){
            $page = max(1, intval($page));
            $offset = ($page - 1) * $this->itemsPerPage;

            $parentGuardians = $this->teacherGuardians->getWithPagination($teacher_id, $offset, $this->itemsPerPage);
            $totalCount = $this->teacherGuardians->getTotalCount($teacher_id);
            $totalPages = max(1, ceil($totalCount / $this->itemsPerPage));

            return [
                'parentGuardians' => $parentGuardians,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalRecords' => $totalCount,
                'itemsPerPage' => $this->itemsPerPage,
                'hasNextPage' => $page < $totalPages,
                'hasPrevPage' => $page > 1
            ];
        }

        /**
         * Search this teacher's parent/guardian records
         * @param int $teacher_id
         * @param string $keyword
         * @return array
         */
        public function searchGuardians($teacher_id, $keyword){
            return $this->teacherGuardians->searchGuardians($teacher_id, $keyword);
        }

        /**
         * Search this teacher's students who don't yet have a guardian record
         * @param int $teacher_id
         * @param string $keyword
         * @return array
         */
        public function searchAvailableStudents($teacher_id, $keyword){
            return $this->teacherGuardians->searchAvailableStudents($teacher_id, $keyword);
        }

        /**
         * Check if a student (scoped to this teacher) already has a guardian record
         * @param int $teacher_id
         * @param int $student_id
         * @return bool
         */
        public function guardianExists($teacher_id, $student_id){
            if(!$this->teacherStudents->isStudentOwnedByTeacher($student_id, $teacher_id)){
                return true; // treat unowned students as unavailable, same effect as "already taken"
            }
            return $this->teacherGuardians->studentHasGuardian($student_id);
        }

        /**
         * Create a parent/guardian record, scoped to students assigned to this teacher
         * @param array $data
         * @param int $teacher_id
         */
        public function create($data, $teacher_id){
            try{
                if(!$this->teacherStudents->isStudentOwnedByTeacher($data['student_id'], $teacher_id)){
                    FlashMessage::setFlash("error", "You can only add guardians for your own students.");
                    header("Location: " . BASE_URL . "/resources/views/teachers/parent-guardians.php");
                    exit();
                }

                if($this->parentGuardians->create($data)){
                    $this->auditLogs->log(
                        $_SESSION['id'] ?? null,
                        $_SESSION['role'] ?? 'unknown',
                        'CREATE PARENT/GUARDIAN',
                        'PARENTS_GUARDIANS',
                        null,
                        'parents_guardians',
                        ($_SESSION['full_name'] ?? 'A teacher') . ' created a new parent/guardian record for student ID: ' . $data['student_id']
                    );
                    FlashMessage::setFlash("success", "Parent/Guardian record created successfully.");
                }else{
                    FlashMessage::setFlash("error", "Error creating Parent/Guardian record.");
                }
                header("Location: " . BASE_URL . "/resources/views/teachers/parent-guardians.php");
                exit();
            }catch(Exception $e){
                ErrorHandler::log($e, 'teacher/ParentGuardiansController::create');
            }
        }

        /**
         * Update a parent/guardian record, scoped to students assigned to this teacher
         * @param int $id parents_guardians.id
         * @param array $data
         * @param int $teacher_id
         */
        public function update($id, $data, $teacher_id){
            try{
                if(!$this->teacherStudents->isStudentOwnedByTeacher($data['student_id'], $teacher_id)){
                    FlashMessage::setFlash("error", "You can only update guardians for your own students.");
                    header("Location: " . BASE_URL . "/resources/views/teachers/parent-guardians.php");
                    exit();
                }

                if($this->parentGuardians->update($id, $data)){
                    $this->auditLogs->log(
                        $_SESSION['id'] ?? null,
                        $_SESSION['role'] ?? 'unknown',
                        'UPDATE PARENT/GUARDIAN',
                        'PARENTS_GUARDIANS',
                        $id,
                        'parents_guardians',
                        ($_SESSION['full_name'] ?? 'A teacher') . ' updated parent/guardian record with ID: ' . $id
                    );
                    FlashMessage::setFlash("success", "Parent/Guardian record updated successfully.");
                }else{
                    FlashMessage::setFlash("error", "Error updating Parent/Guardian record.");
                }
                header("Location: " . BASE_URL . "/resources/views/teachers/parent-guardians.php");
                exit();
            }catch(Exception $e){
                ErrorHandler::log($e, 'teacher/ParentGuardiansController::update');
            }
        }
    }

    //=================================== boostrap the controller ====================================//
    try{
        $teacher_id = $_SESSION['id'] ?? 0;
        $controller = new ParentGuardiansController($con);
        $currentPage = isset($_GET['page']) ? $_GET['page'] : 1;
        $paginationData = $controller->index($teacher_id, $currentPage);
        $parentGuardians = $paginationData['parentGuardians'];
        $pagination = [
            'currentPage' => $paginationData['currentPage'],
            'totalPages' => $paginationData['totalPages'],
            'totalRecords' => $paginationData['totalRecords'],
            'itemsPerPage' => $paginationData['itemsPerPage'],
            'hasNextPage' => $paginationData['hasNextPage'],
            'hasPrevPage' => $paginationData['hasPrevPage']
        ];

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            // Search this teacher's guardian records (main table search)
            if(isset($_POST['search_guardians'])){
                header('Content-Type: application/json');
                echo json_encode($controller->searchGuardians($teacher_id, $_POST['keyword'] ?? ''));
                exit();
            }

            // Search this teacher's students with no guardian yet
            if(isset($_POST['search_available_students'])){
                header('Content-Type: application/json');
                echo json_encode($controller->searchAvailableStudents($teacher_id, $_POST['keyword'] ?? ''));
                exit();
            }

            // Check if a student already has a guardian record
            if(isset($_POST['check_guardian_exists'])){
                header('Content-Type: application/json');
                $studentId = (int)($_POST['student_id'] ?? 0);
                echo json_encode(['exists' => $controller->guardianExists($teacher_id, $studentId)]);
                exit();
            }

            if(isset($_POST['save_guardian']) || isset($_POST['update_guardian'])){
                Csrf::requireValidOnPost(BASE_URL . '/resources/views/teachers/parent-guardians.php');
            }

            if(isset($_POST['save_guardian'])){
                $controller->create(
                    [
                        'student_id' => $_POST['student_id'],
                        'father_name' => $_POST['father_name'],
                        'father_occupation' => $_POST['father_occupation'],
                        'father_contact' => $_POST['father_contact'],
                        'mother_name' => $_POST['mother_name'],
                        'mother_occupation' => $_POST['mother_occupation'],
                        'mother_contact' => $_POST['mother_contact'],
                        'guardian_name' => $_POST['guardian_name'],
                        'guardian_relationship' => $_POST['guardian_relationship'],
                        'guardian_contact' => $_POST['guardian_contact']
                    ],
                    $teacher_id
                );
            }

            if(isset($_POST['update_guardian'])){
                $guardianId = $_POST['id'];
                $controller->update(
                    $guardianId,
                    [
                        'student_id' => $_POST['student_id'],
                        'father_name' => $_POST['father_name'],
                        'father_occupation' => $_POST['father_occupation'],
                        'father_contact' => $_POST['father_contact'],
                        'mother_name' => $_POST['mother_name'],
                        'mother_occupation' => $_POST['mother_occupation'],
                        'mother_contact' => $_POST['mother_contact'],
                        'guardian_name' => $_POST['guardian_name'],
                        'guardian_relationship' => $_POST['guardian_relationship'],
                        'guardian_contact' => $_POST['guardian_contact']
                    ],
                    $teacher_id
                );
            }
        }
    }catch(Exception $e){
        ErrorHandler::log($e, 'teacher/ParentGuardiansController (bootstrap)');
    }
?>

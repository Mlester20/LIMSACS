<?php
session_start();

require_once __DIR__ . '/../../models/registrar/ParentGuardiansModel.php';
require_once __DIR__ . '/../../models/registrar/StudentsModel.php';
require_once __DIR__ . '/../../services/StudentsService.php';
require_once __DIR__ . '/../../helpers/flashMessage.php';
require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../../core/errorHandler.php';
require_once __DIR__ . '/../../helpers/auditLogs.php';
require_once __DIR__ . '/../../helpers/csrf.php';
require_once __DIR__ . '/../../../database/config/config.php';

    class ParentGuardiansController extends Controller{
        protected $auditLogs;
        private $itemsPerPage = 10;
        protected $students;

        public function __construct($con){
            parent::__construct(
                new ParentGuardiansModel($con)
            );
            $this->auditLogs = new AuditLogs($con);
            $this->students = new StudentsModel($con);
        }

        /**
         * Get paginated parent guardians with pagination metadata
         * @param int $page Current page number
         * @return array Pagination data with parent guardians
         */
        public function index($page = 1){
            // Ensure page is at least 1
            $page = max(1, intval($page));

            // Calculate offset
            $offset = ($page - 1) * $this->itemsPerPage;

            // Get paginated parent guardians
            $parentGuardians = $this->model->getWithPagination($offset, $this->itemsPerPage);

            // Get total count
            $totalCount = $this->model->getTotalCount();
            $totalPages = ceil($totalCount / $this->itemsPerPage);

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

        public function getStudents(){
            return $this->students->index();
        }

        public function create($data){
            try{
                if($this->model->create($data)){
                    $this->auditLogs->log(
                        $_SESSION['id'] ?? null,
                        $_SESSION['role'] ?? 'unknown',
                        'CREATE PARENT/GUARDIAN',
                        'PARENTS_GUARDIANS',
                        null,
                        'parents_guardians',
                        $_SESSION['full_name'] . ' created a new parent/guardian record for student ID: ' . $data['student_id'],
                    );
                    FlashMessage::setFlash("success", "Parent/Guardian record created successfully.");
                    header("Location: " . BASE_URL . "/resources/views/registrar/parent-guardians.php");
                    exit();
                }else{
                    FlashMessage::setFlash("error", "Error creating Parent/Guardian record.");
                    header("Location: " . BASE_URL . "/resources/views/registrar/parent-guardians.php");
                    exit();
                }
            }catch(Exception $e){
                ErrorHandler::log($e, 'ParentGuardiansController::create');
            }
        }

        public function update($id, $data){
            try{
                if($this->model->update($id, $data)){
                    $this->auditLogs->log(
                        $_SESSION['id'] ?? null,
                        $_SESSION['role'] ?? 'unknown',
                        'UPDATE PARENT/GUARDIAN',
                        'PARENTS_GUARDIANS',
                        $id,
                        'parents_guardians',
                        $_SESSION['full_name'] . ' updated parent/guardian record with ID: ' . $id,
                    );
                    FlashMessage::setFlash("success", "Parent/Guardian record updated successfully.");
                    header("Location: " . BASE_URL . "/resources/views/registrar/parent-guardians.php");
                    exit();
                }else{
                    FlashMessage::setFlash("error", "Error updating Parent/Guardian record.");
                    header("Location: " . BASE_URL . "/resources/views/registrar/parent-guardians.php");
                    exit();
                }
            }catch(Exception $e){
                ErrorHandler::log($e, 'ParentGuardiansController::update');
            }
        }

        public function delete($id){
            try{
                if($this->model->delete($id)){
                    $this->auditLogs->log(
                        $_SESSION['id'] ?? null,
                        $_SESSION['role'] ?? 'unknown',
                        'DELETE PARENT/GUARDIAN',
                        'PARENTS_GUARDIANS',
                        null,
                        'parents_guardians',
                        $_SESSION['full_name'] . ' deleted parent/guardian record with ID: ' . $id,
                    );
                    FlashMessage::setFlash("success", "Parent/Guardian record deleted successfully.");
                    header("Location: " . BASE_URL . "/resources/views/registrar/parent-guardians.php");
                    exit();
                }else{
                    FlashMessage::setFlash("error", "Error deleting Parent/Guardian record.");
                    header("Location: " . BASE_URL . "/resources/views/registrar/parent-guardians.php");
                    exit();
                }
            }catch(Exception $e){
                ErrorHandler::log($e, 'ParentGuardiansController::delete');
                return false;
            }
        }
    }

    try{
        $controller = new ParentGuardiansController($con);
        $students = $controller->getStudents();

        $currentPage = isset($_GET['page']) ? $_GET['page'] : 1;
        $paginationData = $controller->index($currentPage);
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
            // Search available students (no guardian yet)
            if (isset($_POST['search_available_students'])) {
                $keyword = $_POST['keyword'] ?? '';
                $service = new StudentsService($con);
                $results = $service->searchAvailableStudents($keyword);
                header('Content-Type: application/json');
                echo json_encode($results);
                exit();
            }

            // Check if student already has a guardian record
            if (isset($_POST['check_guardian_exists'])) {
                $studentId = (int)($_POST['student_id'] ?? 0);
                $query = "SELECT COUNT(*) as total FROM parents_guardians WHERE student_id = ?";
                $stmt = $con->prepare($query);
                $stmt->bind_param('i', $studentId);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                header('Content-Type: application/json');
                echo json_encode(['exists' => $row['total'] > 0]);
                exit();
            }

            if(isset($_POST['save_guardian']) || isset($_POST['update_guardian']) || isset($_POST['delete_guardian'])){
                Csrf::requireValidOnPost(BASE_URL . '/resources/views/registrar/parent-guardians.php');
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
                    ]
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
                    ]
                );
            }

            if(isset($_POST['delete_guardian'])){
                $guardianId = $_POST['id'];
                $controller->delete($guardianId);
            }
        }
    }catch(Exception $e){
        ErrorHandler::log($e, 'ParentGuardiansController (bootstrap)');
    }
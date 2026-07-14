<?php
session_start();

require_once __DIR__ . '/../../models/registrar/SectionsModel.php';
require_once __DIR__ . '/../../models/registrar/AcademicHistoryModel.php';
require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../../core/errorHandler.php';
require_once __DIR__ . '/../../helpers/auditLogs.php';
require_once __DIR__ . '/../../helpers/fLashMessage.php';
require_once __DIR__ . '/../../helpers/csrf.php';
require_once __DIR__ . '/../../services/SectionService.php';
require_once __DIR__ . '/../../../database/config/config.php';

    class SectionsController extends Controller{
        protected $auditLogs;
        protected $academicHistory;
        protected $sectionService;

        public function __construct($con){
            parent::__construct(
                new SectionsModel($con)
            );
            $this->auditLogs = new AuditLogs($con);
            $this->academicHistory = new AcademicHistoryModel($con);
            $this->sectionService = new SectionService($con);
        }

        public function index(){
            return $this->model->index();
        }

        public function create($data){
            try{
                if($this->model->create($data)){
                    $this->auditLogs->log(
                        $_SESSION['id'] ?? null,
                        $_SESSION['role'] ?? 'unknown',
                        'CREATE SECTION',
                        'SECTIONS',
                        null,
                        'sections',
                        $_SESSION['full_name'] . ' created section: ' . $data['section_name'],
                    );
                    FlashMessage::setFlash('success', 'Section created successfully.');
                    header('Location: ' . BASE_URL . '/resources/views/registrar/sections.php');
                    exit();
                }else{
                    FlashMessage::setFlash("error", "Failed to create section. Please try again.");
                    header('Location: ' . BASE_URL . '/resources/views/registrar/sections.php');
                    exit();
                }
            }catch(Exception $e){
                ErrorHandler::log($e, 'SectionsController::create');
                return false;
            }
        }

        public function getAvailableTeachers(){
            return $this->model->getAvailableTeachers();
        }

        public function getAllTeachers(){
            return $this->model->getAllTeachers();
        }

        public function getActiveSchoolYear(){
            return $this->model->getActiveSchoolYear();
        }

        public function getStudentTotal(){
            return $this->academicHistory->getTotalCount();
        }

        /**
         * Get sections with enrollment counts and capacity mapped
         * @return array - Sections with total_students and max_capacity fields
         */
        public function getSectionsWithEnrollment(){
            $sections = $this->index();
            $total_counts = $this->getStudentTotal();
            return $this->sectionService->mapEnrollmentToSections($sections, $total_counts);
        }

        public function update($id, $data){
            try{
                if($this->model->update($id, $data)){
                    $this->auditLogs->log(
                        $_SESSION['id'] ?? null,
                        $_SESSION['role'] ?? 'unknown',
                        'UPDATE SECTION',
                        'SECTIONS',
                        null,
                        'sections',
                        $_SESSION['full_name'] . ' updated section: ' . $data['section_name'],
                    );
                    FlashMessage::setFlash('success', 'Section updated successfully.');
                    header('Location: ' . BASE_URL . '/resources/views/registrar/sections.php');
                    exit();
                }else{
                    FlashMessage::setFlash("error", "Failed to update section. Please try again.");
                    header('Location: ' . BASE_URL . '/resources/views/registrar/sections.php');
                    exit();
                }
            }catch(Exception $e){
                ErrorHandler::log($e, 'SectionsController::update');
                exit();
            }
        }

        public function delete($id){
            try{
                if($this->model->delete($id)){
                    $this->auditLogs->log(
                        $_SESSION['id'] ?? null,
                        $_SESSION['role'] ?? 'unknown',
                        'DELETE SECTION',
                        'SECTIONS',
                        null,
                        'sections',
                        $_SESSION['full_name'] . ' deleted section',
                    );
                    FlashMessage::setFlash('success', 'Section deleted successfully.');
                    header('Location: ' . BASE_URL . '/resources/views/registrar/sections.php');
                    exit();
                }else{
                    FlashMessage::setFlash("error", "Failed to delete section. Please try again.");
                    header('Location: ' . BASE_URL . '/resources/views/registrar/sections.php');
                    exit();
                }
            }catch(Exception $e){
                ErrorHandler::log($e, 'SectionsController::delete');
                return false;
            }
        }

    }
    

    //bootstrap the controller
    try{
        $controller = new SectionsController($con);

        // Handle POST requests first
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            if(isset($_POST['save_section']) || isset($_POST['update_section']) || isset($_POST['delete_section'])){
                Csrf::requireValidOnPost(BASE_URL . '/resources/views/registrar/sections.php');
            }

            if(isset($_POST['save_section'])){
                $controller->create(
                    [
                        'section_name' => $_POST['section_name'],
                        'grade_level' => $_POST['grade_level'],
                        'adviser_id' => $_POST['adviser_id'],
                        'school_year_id' => $_POST['school_year_id'],
                        'max_students' => $_POST['max_students'] ?? 35
                    ]
                );
                exit();
            }
            
            if(isset($_POST['update_section'])){
                $update_id = $_POST['id'];
                $controller->update($update_id, [
                    'section_name' => $_POST['section_name'],
                    'grade_level' => $_POST['grade_level'],
                    'adviser_id' => $_POST['adviser_id'],
                    'school_year_id' => $_POST['school_year_id'],
                    'max_students' => $_POST['max_students'] ?? 35
                ]);
                exit();
            }

            if(isset($_POST['delete_section'])){
                $delete_id = $_POST['delete_section'];
                $controller->delete($delete_id);
                exit();
            }
        }

        $sections = $controller->getSectionsWithEnrollment();
        $teachers = $controller->getAvailableTeachers();
        $allTeachers = $controller->getAllTeachers();
        $sy = $controller->getActiveSchoolYear();

    }catch(Exception $e){
        ErrorHandler::log($e, 'SectionsController (bootstrap)');
        exit();
    }
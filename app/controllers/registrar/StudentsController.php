<?php
session_start();

require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../../../database/config/config.php';
require_once __DIR__ . '/../../helpers/message.php';
require_once __DIR__ . '/../../models/registrar/StudentsModel.php';
require_once __DIR__ . '/../../services/StudentsService.php';

    class StudentsController extends Controller{
        private $itemsPerPage = 10;

        public function __construct($con){
            parent::__construct(
                new StudentsModel($con)
            );
        }

        /**
         * Get paginated students with pagination metadata
         * @param int $page Current page number
         * @return array Pagination data with students
         */
        public function index($page = 1){
            // Ensure page is at least 1
            $page = max(1, intval($page));

            // Calculate offset
            $offset = ($page - 1) * $this->itemsPerPage;

            // Get paginated students
            $students = $this->model->getPaginated($this->itemsPerPage, $offset);

            // Add full names
            $studentsWithFullNames = StudentsService::addFullNames($students);

            // Get total count
            $totalCount = $this->model->getTotalCount();
            $totalPages = ceil($totalCount / $this->itemsPerPage);

            return [
                'students' => $studentsWithFullNames,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalRecords' => $totalCount,
                'itemsPerPage' => $this->itemsPerPage,
                'hasNextPage' => $page < $totalPages,
                'hasPrevPage' => $page > 1
            ];
        }

        public function create($data){
            try{
                if($this->model->create($data)){
                    setFlash("success", "Student enrolled successfully.");
                    header("Location: ../../../resources/views/registrar/student-records.php");
                    exit();
                }else{
                    setFlash("error", "Failed to enroll student. Please try again.");
                    header("Location: ../../../resources/views/registrar/student-records.php");
                    exit();
                }
            }catch(Exception $e){
                error_log($e->getMessage());
                return false;
            }
        }


        public function update($id, $data, $page = 1){
            try{
                if($this->model->update($id, $data)){
                    setFlash("success", "Student record updated successfully.");
                    header("Location: ../../../resources/views/registrar/student-records.php?page=" . intval($page));
                    exit();
                }else{
                    setFlash("error", "Failed to update student record. Please try again.");
                    header("Location: ../../../resources/views/registrar/student-records.php?page=" . intval($page));
                    exit();
                }
            }catch(Exception $e){
                error_log($e->getMessage());
                return false;
            }
        }

        public function delete($id, $page = 1){
           try{
                if($this->model->delete($id)){
                    setFlash("success", "Student record deleted successfully.");
                    header("Location: ../../../resources/views/registrar/student-records.php?page=" . intval($page));
                    exit();
                }else{
                    setFlash("error", "Failed to delete student record. Please try again.");
                    header("Location: ../../../resources/views/registrar/student-records.php?page=" . intval($page));
                    exit();
                }
           }catch(Exception $e){
               error_log($e->getMessage());
               return false;
           }
        }
    }

    //=================================== boostrap the controller ====================================//
    try{
        $controller = new StudentsController($con);
        $currentPage = isset($_GET['page']) ? $_GET['page'] : 1;
        $paginationData = $controller->index($currentPage);
        $students = $paginationData['students'];
        $pagination = [
            'currentPage' => $paginationData['currentPage'],
            'totalPages' => $paginationData['totalPages'],
            'totalRecords' => $paginationData['totalRecords'],
            'itemsPerPage' => $paginationData['itemsPerPage'],
            'hasNextPage' => $paginationData['hasNextPage'],
            'hasPrevPage' => $paginationData['hasPrevPage']
        ];

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            if(isset($_POST['enroll_student'])){
                $controller->create(
                    [
                        "lrn" => $_POST['lrn'],
                        "first_name" => $_POST['first_name'],
                        "middle_name" => $_POST['middle_name'],
                        "last_name" => $_POST['last_name'],
                        "suffix" => $_POST['suffix'],
                        "gender" => $_POST['gender'],
                        "birth_date" => $_POST['birth_date'],
                        "age" => $_POST['age'],
                        "place_of_birth" => $_POST['place_of_birth'],
                        "nationality" => $_POST['nationality'],
                        "religion" => $_POST['religion'],
                        "address" => $_POST['address'],
                        "contact_number" => $_POST['contact_number']
                    ]
                );
            }

            if(isset($_POST['edit_student'])){
                $studentId = $_POST['student_id'];
                $controller->update(
                    $studentId,
                    [
                        "lrn" => $_POST['lrn'],
                        "first_name" => $_POST['first_name'],
                        "middle_name" => $_POST['middle_name'],
                        "last_name" => $_POST['last_name'],
                        "suffix" => $_POST['suffix'],
                        "gender" => $_POST['gender'],
                        "birth_date" => $_POST['birth_date'],
                        "age" => $_POST['age'],
                        "place_of_birth" => $_POST['place_of_birth'],
                        "nationality" => $_POST['nationality'],
                        "religion" => $_POST['religion'],
                        "address" => $_POST['address'],
                        "contact_number" => $_POST['contact_number']
                    ],
                    $currentPage
                );
            }

            if(isset($_POST['delete_student'])){
                $studentId = $_POST['student_id'];
                $controller->delete($studentId, $currentPage);

            }
        }
    }catch(Exception $e){
        error_log($e->getMessage());
        exit();
    }
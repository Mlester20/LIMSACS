<?php
session_start();

require_once __DIR__ . '/../../../database/config/config.php';
require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../../models/admin/SchoolYearModel.php';
require_once __DIR__ . '/../../helpers/flashMessage.php';
require_once __DIR__ . '/../../helpers/csrf.php';

const VALID_SY_STATUSES = ['active', 'inactive', 'archived'];

    class SchoolYearController extends Controller{
        public function __construct($con){
            parent::__construct(
                new SchoolYearModel($con)
            );
        }

        public function index($search = '', $status = '', $page = 1){
            $limit = 10;
            $page = max(1, (int)$page);

            $totalRecords = $this->model->count($search, $status);
            $totalPages = $totalRecords > 0 ? (int)ceil($totalRecords / $limit) : 1;
            $page = min($page, $totalPages);
            $offset = ($page - 1) * $limit;

            return [
                'records'       => $this->model->index($search, $status, $limit, $offset) ?: [],
                'current_page'  => $page,
                'total_pages'   => $totalPages,
                'total_records' => $totalRecords,
                'limit'         => $limit,
            ];
        }

        /**
         * @return string[] validation error messages, empty if valid
         */
        public function validate($data){
            $errors = [];

            if(empty(trim($data['school_year'] ?? ''))){
                $errors[] = 'School year is required.';
            }

            $start = DateTime::createFromFormat('Y-m-d', $data['start_date'] ?? '');
            $end = DateTime::createFromFormat('Y-m-d', $data['end_date'] ?? '');

            if(!$start){
                $errors[] = 'Start date is invalid.';
            }
            if(!$end){
                $errors[] = 'End date is invalid.';
            }
            if($start && $end && $start >= $end){
                $errors[] = 'Start date must be before end date.';
            }

            if(!in_array($data['status'] ?? '', VALID_SY_STATUSES, true)){
                $errors[] = 'Please select a valid status.';
            }

            return $errors;
        }

        public function create($data){
            try{
                $errors = $this->validate($data);
                if(!empty($errors)){
                    FlashMessage::setFlash('error', implode(' ', $errors));
                    header('Location: ../../../resources/views/admin/school-year.php');
                    exit();
                }

                if($this->model->create($data)){
                    FlashMessage::setFlash('success', 'School year created successfully');
                    header("Location: ../../../resources/views/admin/school-year.php");
                    exit();
                }else{
                    FlashMessage::setFlash('error', 'Failed to create school year');
                    exit();
                }
            }catch(Exception $e){
                error_log($e->getMessage());
                exit();
            }
        }

        //function to avoid deleting active school year
        public function canDelete($id){
            $school_years = $this->model->index();
            foreach($school_years as $sy){
                if($sy['id'] == $id && $sy['status'] == 'active'){
                    return false;
                }
            }
            return true;
        }

        /**
         * Function to check if an active school year already exists
         * @param string $status
         * @param int|null $except_id
         * @return bool
         */
        public function checkActiveSy($status, $except_id = null) {
            return $this->model->activeSy($status, $except_id);
        }

        public function update($id, $data){
            try{
                $errors = $this->validate($data);
                if(!empty($errors)){
                    FlashMessage::setFlash('error', implode(' ', $errors));
                    header('Location: ../../../resources/views/admin/school-year.php');
                    exit();
                }

                if($this->model->update($id, $data)){
                    FlashMessage::setFlash('success', 'School year updated successfully');
                    header("Location: ../../../resources/views/admin/school-year.php");
                    exit();
                }else{
                    FlashMessage::setFlash('error', 'Failed to update school year');
                    exit();
                }
            }catch(Exception $e){
                error_log($e->getMessage());
                exit();
            }
        }

        public function delete($id){
            try{
                if(!$this->canDelete($id)){
                    FlashMessage::setFlash('error', 'This is an active school year and cannot be deleted.');
                    header('Location: ../../../resources/views/admin/school-year.php');
                    exit();
                }

                if($this->model->delete($id)){
                    FlashMessage::setFlash('success', 'School year deleted successfully');
                    header("Location: ../../../resources/views/admin/school-year.php");
                    exit();                    
                }else{
                    FlashMessage::setFlash('error', 'Failed to delete school year');
                    header("Location: ../../../resources/views/admin/school-year.php");
                    exit();
                }
            }catch(Exception $e){
                error_log($e->getMessage());
                exit();
            }
        }
    }

    //============ bootstrap ============//
    try{
        $controller = new SchoolYearController($con);

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            Csrf::requireValidOnPost('../../../resources/views/admin/school-year.php');

            if(isset($_POST['create_sy'])){
                $controller->create([
                    'school_year' => $_POST['school_year'],
                    'start_date' => $_POST['start_date'],
                    'end_date' => $_POST['end_date'],
                    'status' => $_POST['status']
                ]);
            }
            if (isset($_POST['update_sy'])) {
                $sy_id = $_POST['id'] ?? null;
                $new_status = $_POST['status'] ?? 'inactive';

                if ($new_status === 'active') {
                    //Check if ANOTHER school year is already active
                    if ($controller->checkActiveSy('active', $sy_id)) {
                        FlashMessage::setFlash("error", "Another School Year is already active. ");
                        header("Location: ../../../resources/views/admin/school-year.php");
                        exit; 
                    }
                }

                // 3. If it passes the check, proceed with update
                $controller->update(
                    $sy_id,
                    [
                        'school_year' => $_POST['school_year'],
                        'start_date'  => $_POST['start_date'],
                        'end_date'    => $_POST['end_date'],
                        'status'      => $new_status
                    ]
                );
            }
            if(isset($_POST['delete_sy'])){
                $controller->delete($_POST['id']);
            }
        }

        $search_term = trim($_GET['search'] ?? '');
        $status_filter = $_GET['status'] ?? '';
        $page = $_GET['page'] ?? 1;

        $listing = $controller->index($search_term, $status_filter, $page);
        $school_years = $listing['records'];
        $current_page = $listing['current_page'];
        $total_pages = $listing['total_pages'];
        $total_records = $listing['total_records'];
    }catch(Exception $e){
        error_log($e->getMessage());
    }
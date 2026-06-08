<?php
session_start();

require_once __DIR__ . '/../../../database/config/config.php';
require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../../models/admin/SchoolYearModel.php';
require_once __DIR__ . '/../../helpers/message.php';

    class SchoolYearController extends Controller{
        public function __construct($con){
            parent::__construct(
                new SchoolYearModel($con)
            );
        }

        public function index(){
            return $this->model->index();
        }

        public function create($data){
            try{
                if($this->model->create($data)){
                    setFlash('success', 'School year created successfully');
                    header("Location: ../../../resources/views/admin/school-year.php");
                    exit();
                }else{
                    setFlash('error', 'Failed to create school year');
                    exit();
                }
            }catch(Exception $e){
                error_log($e->getMessage());
                exit();
            }
        }

        //function to check if there's an active sy, data should be abled to insert
        // public function canInsert(){

        // }

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

        public function update($id, $data){
            try{
                if($this->model->update($id, $data)){
                    setFlash('success', 'School year updated successfully');
                    header("Location: ../../../resources/views/admin/school-year.php");
                    exit();
                }else{
                    setFlash('error', 'Failed to update school year');
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
                    setFlash('error', 'This is an active school year and cannot be deleted.');
                    header('Location: ../../../resources/views/admin/school-year.php');
                    exit();
                }

                if($this->model->delete($id)){
                    setFlash('success', 'School year deleted successfully');
                    header("Location: ../../../resources/views/admin/school-year.php");
                    exit();                    
                }else{
                    setFlash('error', 'Failed to delete school year');
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
        $school_years = $controller->index();

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            if(isset($_POST['create_sy'])){
                $controller->create([
                    'school_year' => $_POST['school_year'],
                    'start_date' => $_POST['start_date'],
                    'end_date' => $_POST['end_date'],
                    'status' => $_POST['status']
                ]);
            }
            if(isset($_POST['update_sy'])){
                $sy_id = $_POST['id'] ?? null;
                $controller->update(
                    $sy_id,
                    [
                        'school_year' => $_POST['school_year'],
                        'start_date' => $_POST['start_date'],
                        'end_date' => $_POST['end_date'],
                        'status' => $_POST['status'] ?? 'inactive'
                    ]
                );
            }
            if(isset($_POST['delete_sy'])){
                $controller->delete($_POST['id']);
            }
        }
    }catch(Exception $e){
        error_log($e->getMessage());
    }
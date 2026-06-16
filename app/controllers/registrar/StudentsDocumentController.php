<?php
session_start();

require_once __DIR__ . '/../../models/registrar/StudentsDocumentModel.php';
require_once __DIR__ . '/../../models/registrar/StudentsModel.php';
require_once __DIR__ . '/../../models/registrar/DocumentTypesModel.php';
require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../../helpers/auditLogs.php';
require_once __DIR__ . '/../../helpers/fileUpload.php';
require_once __DIR__ . '/../../helpers/flashMessage.php';
require_once __DIR__ . '/../../../database/config/config.php';

    class StudentsDocumentController extends Controller{
        protected $auditLogs;
        protected $students;
        protected $documentTypes;

        public function __construct($con){
            parent::__construct(
                new StudentsDocumentModel($con)
            );
            $this->auditLogs = new AuditLogs($con);
            $this->students = new StudentsModel($con);
            $this->documentTypes = new DocumentTypesModel($con);
        }

        public function index(){
            return $this->model->index();
        }

        public function students(){
            return $this->students->index();
        }

        public function documentTypes(){
            return $this->documentTypes->index();
        }

        public function create($data){
            try{

            }catch(Exception $e){
                error_log("Error " . $e->getMessage());
                return false;
            }
        }

        public function update($id, $data){
            try{

            }catch(Exception $e){
                error_log("Error " . $e->getMessage());
                return false;
            }
        }

        public function delete($id){
            try{

            }catch(Exception $e){
                error_log("Error " . $e->getMessage());
                return false;
            }
        }
    }


    // ============== bootstrap the controller ============= //
    try{
        $controller = new StudentsDocumentController($con);
        $students = $controller->students();
        $document_types = $controller->documentTypes();
    }catch(Exception $e){
        error_log("Error " . $e->getMessage());
        return false;
    }
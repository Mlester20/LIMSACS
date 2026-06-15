<?php
require_once __DIR__ . '/../Model.php';

    class StudentsDocumentModel extends Model{
        protected $student_documents = 'student_documents';
        protected $students = 'students';
        protected $document_types = 'document_types';

        public function index(){
            try{
                
            }catch(Exception $e){
                error_log("Error " . $e->getMessage());
                return false;
            }
        }

        public function create($data){
            try{

            }catch(Exception $e){
                error_log("Error " . $e->getMessage());
                return false;
            }
        }

        /**
         * Create path and store into storage folder
         */



        public function update($id, $data){
            try{

            }catch(Exception $e){
                error_log("Error " . $e->getMessage());
                return false;
            }
        }

        public function delete($id){
            try{
                $query = "DELETE FROM {$this->student_documents} WHERE id = ? ";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                return true;
            }catch(Exception $e){
                error_log("Error " . $e->getMessage());
                return false;
            }
        }
    }
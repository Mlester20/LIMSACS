<?php
require_once __DIR__ . '/../Model.php';

    class StudentsDocumentModel extends Model{
        protected $student_documents = 'student_documents';
        protected $students = 'students';
        protected $document_types = 'document_types';
        protected $user = 'users';

        public function index(){
            try{
                $query ="SELECT
                    sd.*,
                    s.first_name as student_first_name,
                    s.last_name as student_last_name,
                    dt.document_name as document_type_name,
                    u.full_name as uploaded_by_name,
                    sd.uploaded_at
                    FROM {$this->student_documents} sd
                    LEFT JOIN {$this->students} s ON sd.student_id = s.id
                    LEFT JOIN {$this->document_types} dt ON sd.document_type_id = dt.id
                    LEFT JOIN {$this->user} u ON sd.uploaded_by = u.id
                    ORDER BY s.last_name, s.first_name, sd.uploaded_at DESC
                ";
                $stmt = $this->con->prepare($query);
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_all(MYSQLI_ASSOC);
            }catch(Exception $e){
                error_log("Error " . $e->getMessage());
                return false;
            }
        }

        public function create($data){
            try{
                $query = "INSERT INTO {$this->student_documents} (student_id, document_type_id, file_path, status, remarks, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param(
                    "iisssi",
                    $data['student_id'],
                    $data['document_type_id'],
                    $data['file_path'],
                    $data['status'],
                    $data['remarks'],
                    $data['uploaded_by']
                );
                $stmt->execute();
                return true;
            }catch(Exception $e){
                error_log("Error " . $e->getMessage());
                return false;
            }
        }

        public function update($id, $data){
            try{
                // Only update file_path if it's not null (i.e., a new file was uploaded)
                if($data['file_path'] !== null){
                    $query = "UPDATE {$this->student_documents} SET student_id = ?, document_type_id = ?, file_path = ?, status = ?, remarks = ?, uploaded_by = ? WHERE id = ?";
                    $stmt = $this->con->prepare($query);
                    $stmt->bind_param(
                        "iisssii",
                        $data['student_id'],
                        $data['document_type_id'],
                        $data['file_path'],
                        $data['status'],
                        $data['remarks'],
                        $data['uploaded_by'],
                        $id
                    );
                } else {
                    // Don't update file_path, keep existing value
                    $query = "UPDATE {$this->student_documents} SET student_id = ?, document_type_id = ?, status = ?, remarks = ?, uploaded_by = ? WHERE id = ?";
                    $stmt = $this->con->prepare($query);
                    $stmt->bind_param(
                        "iissii",
                        $data['student_id'],
                        $data['document_type_id'],
                        $data['status'],
                        $data['remarks'],
                        $data['uploaded_by'],
                        $id
                    );
                }
                $stmt->execute();
                return true;
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

        public function find($id){
            try{
                $query ="SELECT
                    sd.*,
                    s.first_name as student_first_name,
                    s.last_name as student_last_name,
                    dt.name as document_type_name,
                    u.full_name as uploaded_by_name,
                    sd.uploaded_at
                    FROM {$this->student_documents} sd
                    LEFT JOIN {$this->students} s ON sd.student_id = s.id
                    LEFT JOIN {$this->document_types} dt ON sd.document_type_id = dt.id
                    LEFT JOIN {$this->user} u ON sd.uploaded_by = u.id
                    WHERE sd.id = ?
                ";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_assoc();
            }catch(Exception $e){
                error_log("Error " . $e->getMessage());
                return false;
            }
        }

        public function search($keyword){
            try{
                $search_term = "%" . $keyword . "%";
                $query ="SELECT
                    sd.*,
                    s.first_name as student_first_name,
                    s.last_name as student_last_name,
                    dt.name as document_type_name,
                    u.full_name as uploaded_by_name,
                    sd.uploaded_at
                    FROM {$this->student_documents} sd
                    LEFT JOIN {$this->students} s ON sd.student_id = s.id
                    LEFT JOIN {$this->document_types} dt ON sd.document_type_id = dt.id
                    LEFT JOIN {$this->user} u ON sd.uploaded_by = u.id
                    WHERE s.lrn LIKE ? OR CONCAT(s.first_name, ' ', s.last_name) LIKE ?
                ";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param("ss", $search_term, $search_term);
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_all(MYSQLI_ASSOC);
            }catch(Exception $e){
                error_log("Error " . $e->getMessage());
                return false;
            }
        }
    }